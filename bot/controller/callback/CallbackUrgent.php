<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;

class CallbackUrgent extends AbstractCallback {

    const CALLBACK_DATA_PATTERN = "/^(urgent|urgent_off)$/";
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {

        $ticket = ticket::getByGroupThread(
                $callback_query->getMessage()->getChat()->getId(), 
                $callback_query->getMessage()->getMessageThreadId());
        
        if ($this->matches[1] == 'urgent') {
            $ticket->setUrgent();
        } elseif ($this->matches[1] == 'urgent_off') {
            $ticket->setUrgent(false);
        }
        
        return true;
    }
}
