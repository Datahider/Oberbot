<?php

namespace losthost\Oberbot\controller\display;

use losthost\Oberbot\data\chat_group;
use losthost\Oberbot\data\session;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\DB\DBView;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

use function \losthost\Oberbot\__;

class ActiveListDisplay {
    
    static public function display(int $user_id, int $chat_id, int $message_to_edit=null) {

        $agent_lists = chat_group::getUserLists($user_id);
        $active = self::getActiveList($user_id);
        $active_name = is_null($active) ? __('all') : $active;
        $active_groups = self::getActiveGroups($user_id, $active);
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('viewActiveListDisplay', 'kbdActiveListDisplay', [
            'agent_lists' => $agent_lists,
            'active_list' => $active,
            'active_name' => $active_name,
            'active_groups' => $active_groups
        ], $message_to_edit);
        
    }

    static protected function getActiveGroups(int $user_id, ?string $active) {
        
        $groups = new DBView(self::getActiveGroupsSQLQuery($active), [$active, $user_id]);
        
        $result = [];
        while ($groups->next()) {
            $result[] = [
                'id' => $groups->id,
                'title' => $groups->title
            ];
        }
        
        return $result;
    }
    
    static protected function getActiveGroupsSQLQuery(?string $active) {
        $sql = <<<FIN
                SELECT
                    agents.chat_id AS id,
                    titles.title AS title
                FROM 
                    [user_chat_role] as agents
                    INNER JOIN [telle_chats] as titles ON titles.id = agents.chat_id
                    INNER JOIN [chat_groups] AS chats ON agents.chat_id = chats.chat_id AND chats.chat_group = ?
                WHERE 
                    agents.role = 'agent'
                    AND agents.user_id = ?;                
                FIN;
        
        if (empty($active)) {
            $sql = str_replace("INNER JOIN [chat_groups] AS chats ON agents.chat_id = chats.chat_id AND chats.chat_group = ?", "AND ? IS NULL", $sql);
        }
        
        return $sql;
    }


    static public function getActiveList(int $user_id) {
        $session = new session(['user_id' => $user_id, 'chat_id' => $user_id], true);
        $active = !$session->working_group ? null : $session->working_group;
        return $active;
    }
}
