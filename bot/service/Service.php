<?php

namespace losthost\Oberbot\service;

use losthost\DB\DBView;
use losthost\Oberbot\data\user_chat_role;
use losthost\templateHelper\Template;
use losthost\telle\Bot;
use losthost\BotView\BotView;

class Service {
    
    static public function __($text) {
        $template = new Template('translations.php', Bot::$language_code);
        $template->assign('text', $text);
        return $template->process();
    }


    static public function mentionById(int $tg_id) {

        $user = new DBView("SELECT first_name, last_name, username FROM [telle_users] WHERE id = ?", [$tg_id]);
        if ($user->next()) {
            if ($user->username) {
                return "@$user->username";
            } else {
                return "<a href=tg://user?id=$tg_id>$user->first_name</a>";
            }
        } else {
            return "<a href=tg://user?id=$tg_id>&lt;Неизвестный&gt;</a>";
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

}
