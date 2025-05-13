<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;

class CallbackToTaskTicket extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^(to_task|to_ticket)$/";
    const PERMIT = self::PERMIT_AGENT|self::PERMIT_USER;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $ticket = ticket::getByGroupThread(
                $callback_query->getMessage()->getChat()->getId(), 
                $callback_query->getMessage()->getMessageThreadId());
        
        if ($this->matches[1] == 'to_task') {
            $ticket->setType(ticket::TYPE_REGULAR_TASK);
        } elseif ($this->matches[1] == 'to_ticket') {
            $ticket->setType(ticket::TYPE_MALFUNCTION);
        }
        
        return true;
    }
}
