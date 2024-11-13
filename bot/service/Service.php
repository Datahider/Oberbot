<?php

namespace losthost\Oberbot\service;

use losthost\DB\DBView;
use losthost\Oberbot\data\user_chat_role;
use losthost\templateHelper\Template;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\session;
use losthost\Oberbot\data\ticket;
use losthost\DB\DBValue;

class Service {
    
    static public function __($text) {
        $template = new Template('translations.php', Bot::$language_code);
        $template->assign('text', $text);
        return $template->process();
    }


    static public function mentionById(int $tg_id) {

        $user = new DBView("SELECT first_name, last_name, username FROM [telle_users] WHERE id = ?", [$tg_id]);
        if ($user->next()) {
            return "<a href=\"tg://user?id=$tg_id\">$user->first_name</a>";
        } else {
            return "<a href=\"tg://user?id=$tg_id\">&lt;Неизвестный&gt;</a>";
        }

    }

    static public function mentionByIdArray(array $tg_ids, mixed $show_none='') {

        if (!empty($tg_ids)) {
            $text = '';
            foreach ($tg_ids as $tg_id) {
                $text .= ', '. static::mentionById($tg_id);
            }
            return substr($text, 2);
        } 

        return $show_none;
    }
    
    static public function seconds2dateinterval(?int $seconds) : \DateInterval {
        if (is_null($seconds)) {
            $seconds = 0;
        }
        $zero = new \DateTime('@0');
        $offset = new \DateTime("@$seconds");
        return $zero->diff($offset);
    }

    static public function message(string $type, string $text, ?string $header=null, ?int $message_tread_id=null) {
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('tpl_message', null, ['type' => $type, 'header' => Service::__($header), 'text' => Service::__($text)], null, $message_tread_id);
    }

    static public function isAgent($user_id, $chat_id) : bool {
        return static::getUserChatRole($user_id, $chat_id) === user_chat_role::ROLE_AGENT;
    }

    static public function getUserChatRole($user_id, $chat_id) : string {
        $role = new user_chat_role(['user_id' => $user_id, 'chat_id' => $chat_id], true);
        if ($role->isNew()) {
            return user_chat_role::ROLE_CUSTOMER;
        }
        return $role->role;
    }

    static protected function getAgentChatIds(int $user_id) : array {
        
        $chats = new DBView(<<<FIN
            SELECT 
                chat_id 
            FROM 
                [user_chat_role] AS roles 
                LEFT JOIN [chat] AS chats 
                    ON chats.id = roles.chat_id     
            WHERE 
                roles.role = 'agent' 
                AND chats.process_tickets = 1
                AND roles.user_id = ?
            FIN, [$user_id]);
        
        $result_array = [];
        while ($chats->next()) {
            $result_array[] = $chats->chat_id;
        }
        
        return $result_array;
    }
    
    static public function getOldestTicket(int $user_id, ?string $group=null) : ?ticket {
        
        $chat_ids = implode(',', static::getAgentChatIds($user_id));
        $status_new = ticket::STATUS_NEW;
        $status_in_progress = ticket::STATUS_IN_PROGRESS;
        $status_reopen = ticket::STATUS_REOPEN;
        $statuses = "$status_new,$status_reopen,$status_in_progress";
        
        $sql = <<<FIN
                SELECT
                    topics.id,
                    CASE
                        WHEN topics.status = $status_new THEN 1
                        WHEN topics.status = $status_reopen THEN 2
                        ELSE 3
                    END AS status_order
                FROM
                    [topics] AS topics
                    LEFT JOIN [topic_admins] AS admins
                    ON topics.id = admins.topic_number
                    LEFT JOIN [chat_groups] AS groups
                    ON topics.chat_id = groups.chat_id
                    LEFT JOIN [topics] AS waitings
                    ON waitings.id = topics.wait_for
                WHERE
                    topics.status IN ($statuses)
                    AND (
                        topics.last_admin_activity < :now-3600*(1-topics.is_task)
                        AND topics.last_admin_activity < :now-:day_seconds*topics.is_task
                        OR topics.last_admin_activity < topics.last_activity
                    )
                    AND topics.chat_id IN ($chat_ids)
                    AND (topics.status = 0 OR admins.user_id = :user_id)
                    AND (groups.chat_group = :group OR :group IS NULL)
                    AND (topics.wait_till < :datenow OR topics.wait_till IS NULL)
                    AND (waitings.status NOT IN ($statuses) OR waitings.status IS NULL) 
                ORDER BY
                    topics.is_task,
                    status_order - topics.is_urgent ASC,
                    topics.last_admin_activity ASC
                LIMIT 1
                FIN;
                            
        $now = time();
        $datenow = date_create_immutable()->format('Y-m-d H:i:s');
        $day_seconds = $now % 86400;
        
        $params = compact('now', 'day_seconds', 'user_id', 'group', 'datenow');

        $view = new DBView($sql, $params);
        
        if ($view->next()) {
            return ticket::getById($view->id);
        }
        return null;
    }
    
    static public function showNextTicket(int $user_id) {

        $session = new session(['user_id' => $user_id, 'chat_id' => $user_id], true);
        $user = new DBView('SELECT language_code FROM [telle_users] WHERE id=?', [$session->user_id]);
        if ($user->next()) {
            $ticket = static::getOldestTicket($user_id, $session->working_group);

            $view = new BotView(Bot::$api, $user_id, $user->language_code);
            $view->show('controllerCommandNext', null, ['ticket' => $ticket, 'working_group' => $session->working_group]);
        }
    }
    
    static public function ticketMention(ticket $ticket) {
        $url = str_replace('-100', 'c/', "https://t.me/$ticket->chat_id/$ticket->topic_id");
        $link = "<a href=\"$url\">$ticket->title - #$ticket->id</a>";
        return $link;
    }
    
    static public function ticketMentionNoId(ticket $ticket) {
        $url = str_replace('-100', 'c/', "https://t.me/$ticket->chat_id/$ticket->topic_id");
        $link = "<a href=\"$url\">$ticket->title</a>";
        return $link;
    }
    
    static function isUsersChatAccessible(int $user_id) {
        try {
            Bot::$api->sendChatAction($user_id, 'typing');
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    static function getRandomTopicIconColor() : int {
        $options = [7322096, 16766590, 13338331, 9367192, 16749490, 16478047];
        return $options[random_int(0, 5)];
    }
    
    static function getUserDataById(int $user_id) {
        return new DBValue('SELECT * FROM [telle_users] WHERE id = ?', [$user_id]);
    }
    
}
