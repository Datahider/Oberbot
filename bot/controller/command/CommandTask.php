<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandTask extends AbstractAuthCommand {
    
    const COMMAND = 'task';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $this->do(
                $this->args, 
                ticket::getByGroupThread($message->getChat()->getId(), $message->getMessageThreadId()));
        return true;
    }
    
    public function do(string $args, ticket $ticket) {
        switch ($args) {
            case 'off':
            case 'false':
            case '-':
                $ticket->toTicket();
                break;
            case '':
            case 'on':
            case 'true':
            case '+':
                $ticket->toTask();
                break;
            default:
                throw new \Exception("$args?");
        }
        
    }
}
