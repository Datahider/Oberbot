<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;

class CommandDone extends AbstractAuthCommand {
    
    const COMMAND = 'done';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $topic_id = $message->getMessageThreadId();
        
        $ticket = ticket::getByGroupThread($group_id, $topic_id);
        $ticket->close();

        return true;
    }
}
