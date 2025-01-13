<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\Oberbot\data\ticket;

class ForumTopicEdited extends AbstractHandlerMessage {
    
    protected ?string $new_name;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $forum_topic_edited = $message->getForumTopicEdited();
        if ($forum_topic_edited) {
            $this->new_name = $forum_topic_edited->getName();
            if ($this->new_name) {
                return true;
            }
        }
        
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $ticket = ticket::getByGroupThread($message->getChat()->getId(), $message->getMessageThreadId());
        $ticket->title = $this->new_name;
        $ticket->write();
        return true;
    }
}
