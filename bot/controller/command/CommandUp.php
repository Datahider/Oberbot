<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\service\Service;
use losthost\DB\DBList;
use losthost\Oberbot\data\ticket;

class CommandUp extends AbstractAuthCommand {
    
    const COMMAND = 'up';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER; // пользователи тоже должны иметь возможность поднять нерешенные заявки
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        if ($thread_id) {
            Service::message('warning', 'Команда /up предназначена для использования в общем чате группы.');
            return true;
        }
        
        $statuses = implode(', ', [ticket::STATUS_NEW, ticket::STATUS_IN_PROGRESS, ticket::STATUS_REOPEN]);
        $tickets = new DBList(ticket::class, "status IN ($statuses) AND chat_id = ?", [$group_id]);
        
        while ($ticket = $tickets->next()) {
            Service::message('none', sprintf(Service::__('%s поднял заявку.'), Service::mentionById($user_id)), null, $ticket->topic_id);
        }
        
        return true;
    }
}
