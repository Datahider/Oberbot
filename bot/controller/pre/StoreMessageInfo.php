<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\message_info;

class StoreMessageInfo extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        return (bool)$message->getMessageThreadId();
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $message_info = new message_info([
            'chat_id' => $message->getChat()->getId(),
            'message_id' => $message->getMessageId(),
            'thread_id' => $message->getMessageThreadId(),
            'user_id' => $message->getFrom()->getId()
        ], true);
        
        $message_info->write();
        return false;
        
    }
}
