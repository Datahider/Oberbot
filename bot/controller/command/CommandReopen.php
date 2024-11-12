<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandReopen extends AbstractAuthCommand {
    
    const COMMAND = 'reopen';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        
        $ticket = ticket::getByGroupThread($group_id, $thread_id);
        $ticket->reopen();
        
        return true;
    }
}
