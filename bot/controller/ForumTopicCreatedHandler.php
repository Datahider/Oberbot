<?php

namespace losthost\Oberbot\controller;

use losthost\Oberbot\data\ticket;
use losthost\telle\abst\AbstractHandlerMessage;

class ForumTopicCreatedHandler extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getForumTopicCreated()) {
            return true;
        }
        return false;
    }
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageId();
        $title = $message->getForumTopicCreated()->getName();
        
        $ticket = ticket::create($message->getChat()->getId(), $thread_id, $title);
        return true;
    }

}
