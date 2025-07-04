<?php

namespace losthost\Oberbot\controller\command;

class CommandRules extends AbstractAuthCommand {
    
    const COMMAND = 'rules';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        
    }

    protected static function permit(): int {
        return self::PERMIT_ADMIN | self::PERMIT_AGENT | self::PERMIT_USER;
    }

    public static function description(): array {
        return [
            'default' => 'Просмотр/установка правил',
        ];
    }
}
