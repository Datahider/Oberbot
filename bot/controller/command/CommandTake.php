<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandTake extends AbstractAuthCommand {
    
    const COMMAND = 'take';
    const DESCRIPTION = [
        'default' => 'Взятие заявки в работу',
    ];
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);

        $ticket->linkAgent($this->user_id)->timerStart($this->user_id);
        
        return true;
    }
    
    static protected function permit(): int {
        return self::PERMIT;
    }

    static public function description(): array {
        return self::DESCRIPTION;
    }
    
}
