<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandUnlink extends AbstractAuthCommand {
    
    const COMMAND = 'unlink';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        $ticket = ticket::getByGroupThread($group_id, $thread_id);
        $ticket->unlink($user_id);
        
        return true;
    }
}
