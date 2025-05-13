<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandTask extends AbstractAuthCommand {
    
    const COMMAND = 'task';
    const DESCRIPTION = [
        'default' => 'Изменение типа заявки (Задача/Неисправность)',
        'all_group_chats' => 'Изменение типа заявки (Задача/Неисправность)'
    ];
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $this->do(
                $this->args, 
                ticket::getByGroupThread($message->getChat()->getId(), $message->getMessageThreadId()));
        return true;
    }
    
    public function do(string $args, ticket $ticket) {
            $ticket->setType(ticket::TYPE_REGULAR_TASK);
    }
}
