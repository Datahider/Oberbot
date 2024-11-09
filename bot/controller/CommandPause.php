<?php

namespace losthost\Oberbot\controller;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\controller\AbstractAuthCommand;

class CommandPause extends AbstractAuthCommand {

    const COMMAND = 'pause';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        $ticket = ticket::getByGroupThread($group_id, $thread_id);

        $ticket->timerStop($user_id);
        
        return true;
    }
}
