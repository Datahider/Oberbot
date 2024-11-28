<?php

namespace losthost\Oberbot\controller\action;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;

class ActionCreateTicket {
    
    static public function do(int $chat_id, int $user_id, string $title, string $message) {

        $forum_topic = Bot::$api->createForumTopic($chat_id, $title, Service::getRandomTopicIconColor());
        $new_thread = $forum_topic->getMessageThreadId();
        sendMessage($message, null, $chat_id, $new_thread);

        $new_ticket = ticket::create($chat_id, $new_thread, $title, $user_id);
        $new_ticket->linkCustomer($user_id);
        $new_ticket->accept();
    }
}
