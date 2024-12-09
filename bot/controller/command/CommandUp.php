<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\service\Service;
use losthost\DB\DBList;
use losthost\DB\DBView;
use losthost\Oberbot\data\ticket;

class CommandUp extends AbstractAuthCommand {
    
    const COMMAND = 'up';
    const DESCRIPTION = [
        'default' => '«Поднятие» открытых заявок',
        'all_group_chats' => '«Поднятие» открытых заявок'
    ];
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER; // пользователи тоже должны иметь возможность поднять нерешенные заявки
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        if ($this->thread_id > 1) {
            Service::message('warning', 'Команда /up предназначена для использования в общем чате группы.');
            return true;
        }
        
        $statuses = implode(', ', [ticket::STATUS_NEW, ticket::STATUS_IN_PROGRESS, ticket::STATUS_REOPEN, ticket::STATUS_AWAITING_USER, ticket::STATUS_USER_ANSWERED]);
        
        $sql = <<<FIN
                SELECT 
                    tickets.topic_id AS topic_id 
                FROM 
                    [topics] AS tickets
                    LEFT JOIN [topics] AS wait_for ON wait_for.id = tickets.wait_for
                WHERE 
                    tickets.chat_id = ?
                    AND tickets.status IN ($statuses)
                    AND (tickets.wait_till IS NULL OR tickets.wait_till < NOW())
                    AND (wait_for.status IS NULL OR wait_for.status NOT IN ($statuses))
                FIN;
        
        $ticket = new DBView($sql, [$this->chat_id]);
        
        while ($ticket->next()) {
            Service::message('none', sprintf(Service::__('%s поднял заявку.'), Service::mentionById($this->user_id)), null, $ticket->topic_id);
        }
        
        return true;
    }
}
