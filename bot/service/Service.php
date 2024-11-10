<?php

namespace losthost\Oberbot\service;

use losthost\DB\DBView;

class Service {
    
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
        $view->show('tpl_message', null, ['type' => $type, 'header' => __($header), 'text' => __($text)], null, $message_tread_id);
    }

}
