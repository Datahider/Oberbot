<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandFree extends CommandTake {
    
    const COMMAND = 'free';
    const DESCRIPTION = [
        'default' => 'Назначение типа заявки "Бесплатная" и взятие её в работу',
    ];
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        parent::handle($message);
        
        $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);        
        $ticket->setType(ticket::TYPE_MALFUNCTION_FREE);
        
        return true;
    }

    static protected function permit(): int {
        return self::PERMIT;
    }

    static public function description(): array {
        return self::DESCRIPTION;
    }
}
