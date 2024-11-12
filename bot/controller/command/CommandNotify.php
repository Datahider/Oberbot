<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;

class CommandNotify extends AbstractAuthCommand {
    
    const COMMAND = 'notify';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $topic_id = $message->getMessageThreadId();
        
        $ticket = ticket::getByGroupThread($group_id, $topic_id);
        $customers = $ticket->getCustomers();
        
        if (!empty($customers)) {
            Service::message('notification', Service::mentionByIdArray($customers), Service::__('<b>Ответьте</b>'), $topic_id);
        } else {
            Service::message('info', 'К тикету не привязан ни один пользователь.', null, $topic_id);
        }
        
        return true;
    }
}
