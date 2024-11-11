<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;

class CallbackRate extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^(bad|acceptable|good)$/";
    const PERMIT = self::PERMIT_USER;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $ticket = ticket::getByGroupThread(
                $callback_query->getMessage()->getChat()->getId(), 
                $callback_query->getMessage()->getMessageThreadId());
        
        $ticket->rate($this->matches[1]);
        return true;
        
    }
}
