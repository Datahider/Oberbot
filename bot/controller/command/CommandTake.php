<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandTake extends AbstractAuthCommand {
    
    const COMMAND = 'take';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        $ticket = ticket::getByGroupThread($group_id, $thread_id);

        $ticket->linkAgent($user_id)->timerStart($user_id);
        
        return true;
    }
}
