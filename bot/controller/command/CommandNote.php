<?php

namespace losthost\Oberbot\controller\command;

class CommandNote extends AbstractAuthCommand {
    
    const COMMAND = 'note';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        // просто ничего не делаем
        return true;
    }

    protected static function permit(): int {
        return self::PERMIT_USER | self::PERMIT_AGENT;
    }

    public static function description(): array {
        return [
            'default' => 'Отправка сообщения без обновления времени последней активности и/или статуса'
        ];
    }
}
