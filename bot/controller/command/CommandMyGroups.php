<?php

namespace losthost\Oberbot\controller\command;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class CommandMyGroups extends AbstractAuthCommand {
    
    const COMMAND = 'mygroups';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        sendMessage(__('Тут будет результат работы команды /mygroups'));
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
