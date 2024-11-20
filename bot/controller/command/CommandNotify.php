<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;
use losthost\telle\Bot;

class CommandNotify extends AbstractAuthCommand {
    
    const COMMAND = 'notify';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $topic_id = $message->getMessageThreadId();
        
        if ($topic_id) {
            $ticket = ticket::getByGroupThread($group_id, $topic_id);
            $customers = $ticket->getCustomers();
        } else {
            $customers = null; // Здесь нужно получать список пользователей чата,
                               // но бот не может их запросить у телеги, значит надо вести самому
                               // Можно обновлять данные при сообщениях пользователей, а ночью 
                               // чистить тех, кто у нас учтён, но уже не является членом группы
                               //
        }   

        if (!empty($customers)) {
            Service::message('notification', Service::mentionByIdArray($customers), Service::__('<b>Ответьте</b>'), $topic_id);
        } else {
            Service::message('info', 'К тикету не привязан ни один пользователь.', null, $topic_id);
        }
        
        return true;
    }
}
