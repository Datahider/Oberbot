<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\view\AcceptingMessage;

class CallbackLink extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^link$/";
    const PERMIT = self::PERMIT_USER;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        $ticket = ticket::getByGroupThread(
                $callback_query->getMessage()->getChat()->getId(),
                $callback_query->getMessage()->getMessageThreadId());

        $customer_id = $callback_query->getFrom()->getId();
        $ticket->linkCustomer($customer_id);
        return true;
    }
}
