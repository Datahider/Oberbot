<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;

class CallbackUserPriority extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^user_priority_([12345])$/";
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $ticket = ticket::getByGroupThread(
                $callback_query->getMessage()->getChat()->getId(), 
                $callback_query->getMessage()->getMessageThreadId());
        
        $ticket->setUserPriority($this->matches[1]);
        
        return true;
        
    }
}
