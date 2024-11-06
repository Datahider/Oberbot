<?php

namespace losthost\Oberbot\controller;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\ticket;

use function \losthost\Oberbot\isAgent;

class FirstTopicMessageHandler extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        
        $topic_id = $message->getMessageThreadId();
        $group_id = $message->getChat()->getId();
       
        $ticket = new ticket(['topic_id' => $topic_id, 'chat_id' => $group_id]);
        if ($ticket->status == ticket::STATUS_CREATING) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $topic_id = $message->getMessageThreadId();
        $group_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        
        $ticket = ticket::touch($group_id, $topic_id, isAgent($user_id, $group_id));
        
        if ($ticket->ticket_creator == $user_id) {
            ticket::accept($group_id, $topic_id);
        } else {
            // TODO -- 
        }
    }
}
