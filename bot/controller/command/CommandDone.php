<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandDone extends AbstractAuthCommand {
    
    const COMMAND = 'done';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER; // Пользователи тоже могут закрывать заявки, почему бы и нет
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $topic_id = $message->getMessageThreadId();
        
        $ticket = ticket::getByGroupThread($group_id, $topic_id);
        $ticket->close();

        return true;
    }
}
