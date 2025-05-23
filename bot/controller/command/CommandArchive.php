<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use function \losthost\Oberbot\__;

class CommandArchive extends AbstractAuthCommand {
    
    const COMMAND = 'archive';
    const DESCRIPTION = [
        'default' => 'Архивирование заявки',
        'all_group_chats' => 'Архивирование заявки'
    ];
    
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    static protected function permit(): int {
        return self::PERMIT;
    }

    static public function description(): array {
        return self::DESCRIPTION;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        try {
            $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
        } catch (\Exception $ex) {
            throw new \Exception(__('Эта команда предназначена для использования внутри заявки.'));
        }
        
        if ($ticket->status == ticket::STATUS_ARCHIVED) {
            return false; // Чтобы сработал запрет на написание
        }
        
        $ticket->archive();
        
        return true;
    }
}
