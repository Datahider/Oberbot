<?php

namespace losthost\Oberbot\controller\action;

use losthost\telle\Bot;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\mentionById;
use function \losthost\Oberbot\ifnull;

class ActionCreateTicket {
    
    static public function do(?int $from_chat_id, int $chat_id, int $user_id, string $title, int|string|array $messages) : ticket {

        $from_chat_id = ifnull($from_chat_id, $chat_id);
        
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        
        $normalized_title = static::normalizeTitle($title);
        $new_thread = static::createTopicInGroup($chat_id, $normalized_title);
        
        foreach ($messages as $message) {
            if (is_int($message)) {
                Bot::$api->forwardMessage($chat_id, $from_chat_id, $message, false, false, $new_thread);
            } else {
                $text = __('От %author%:', mentionById($user_id, true)). "\n";
                sendMessage($text, null, $chat_id, $new_thread);
            }
        }
        
        $new_ticket = ticket::create($chat_id, $new_thread, $normalized_title, $user_id);
        $new_ticket->linkCustomer($user_id);
        $new_ticket->accept();
        
        return $new_ticket;
    }
    
    static public function createTopicInGroup(int $chat_id, string $title, ?int $icon_color=null, ?int $icon=null) {
        if (is_null($icon_color)) {
            $icon_color = Service::getRandomTopicIconColor();
        }
        
        $forum_topic = Bot::$api->createForumTopic($chat_id, static::normalizeTitle($title), $icon_color, $icon);
        $new_thread = $forum_topic->getMessageThreadId();
        return $new_thread;
    }
    
    static protected function normalizeTitle(string $text) {
        $m = [];
        preg_match("/^(.*)$/m", $text, $m);
        
        $first_line = $m[1];
        if (mb_strlen($first_line) > 128) {
            $first_line = mb_substr($first_line, 0, 127). '…';
        }
        return $first_line;
    }
}
