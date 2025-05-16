<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandUrgent extends AbstractAuthCommand {
    
    const COMMAND = 'urgent';
    const DESCRIPTION = [
        'default' => 'Изменение приоритета заявки',
        'all_group_chats' => 'Изменение приоритета заявки'
    ];
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $ticket = ticket::getByGroupThread($message->getChat()->getId(), $message->getMessageThreadId());

        switch ($this->args) {
            case 'off':
            case 'false':
            case '-':
                $ticket->setUrgent(false);
                break;
            case '':
            case 'on':
            case 'true':
            case '+':
                $ticket->setUrgent(true);
                break;
            default:
                throw new \Exception("$this->args?");
        }
        return true;
    }
    
    static protected function permit(): int {
        return self::PERMIT;
    }

    static public function description(): array {
        return self::DESCRIPTION;
    }
    
}
