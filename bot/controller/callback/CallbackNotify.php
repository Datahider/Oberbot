<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\Oberbot\service\Service;

class CallbackNotify extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^notify$/";
    const PERMIT = self::PERMIT_AGENT;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {

        $group_id = $callback_query->getMessage()->getChat()->getId();
        $thread_id = $callback_query->getMessage()->getMessageThreadId();
        $message_id = $callback_query->getMessage()->getMessageId();
        
        $ticket = ticket::getByGroupThread($group_id, $thread_id);
        $customers = $ticket->getCustomers();
        
        if (!empty($customers)) {
            Service::message('notification', Service::mentionByIdArray($customers), Service::__('<b>Ответьте</b>'), $thread_id);
        }
        
        return true;
    }
}
