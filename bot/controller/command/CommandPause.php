<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;

class CommandPause extends AbstractAuthCommand {

    const COMMAND = 'pause';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        $ticket = ticket::getByGroupThread($group_id, $thread_id);
        $ticket->touchAdmin($user_id);
        $ticket->timerStop($user_id);
        
        Service::showNextTicket($message->getFrom()->getId());
        return true;
    }
}
