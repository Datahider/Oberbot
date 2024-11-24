<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;

class CommandPause extends AbstractAuthCommand {

    const COMMAND = 'pause';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
        $ticket->touchAdmin($this->user_id);
        $ticket->timerStop($this->user_id);
        
        Service::showNextTicket($this->user_id);
        return true;
    }
}
