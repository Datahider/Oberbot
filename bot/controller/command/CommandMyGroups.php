<?php

namespace losthost\Oberbot\controller\command;

class CommandMyGroups extends AbstractAuthCommand {
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        return true;
    }

    protected static function permit(): int {
        return self::PERMIT_PRIVATE;   
    }

    public static function description(): array {
        [
            'default' => 'Выводит список групп ролями в группе, обозначенными с помощью Emoji'
        ];
    }

}
