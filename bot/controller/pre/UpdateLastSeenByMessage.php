<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\Oberbot\data\chat_user;

class UpdateLastSeenByMessage extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        
        $chat_id = $message->getChat() ? $message->getChat()->getId() : null;
        $user_id = $message->getFrom() ? $message->getFrom()->getId() : null;
        
        chat_user::update_last_seen($chat_id, $user_id);
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
    }
}
