<?php

namespace losthost\Oberbot\controller\command;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class CommandCall extends AbstractAuthCommand {
    
    const COMMAND = 'call';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        sendMessage(__('Не реализовано'), null, $this->chat_id, $this->thread_id);
        
        return true;
    }

    static protected function permit(): int {
        return self::PERMIT_AGENT;
    }

    static public function description(): array {
        return [
            'default' => 'Создание (под)задачи с типом Консультация'
        ];
    }

}
