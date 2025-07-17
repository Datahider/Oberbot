<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\Oberbot\view\settings\SettingsMainMenu;

class CommandSettings extends AbstractAuthCommand {
    
    const COMMAND = 'settings';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        if ($this->chat_id == $this->user_id) {
            return $this->handlePrivate($message);
        } else {
            return $this->handleGroup($message);
        }
    }
    
    protected function handlePrivate(\TelegramBot\Api\Types\Message &$message): bool {
        $view = new SettingsMainMenu();
        $view->show();
        return true;
    }
    
    protected function handleGroup(\TelegramBot\Api\Types\Message &$message): bool {
        return true;
    }
    

    protected static function permit(): int {
        return self::PERMIT_PRIVATE | self::PERMIT_ADMIN;
    }

    public static function description(): array {
        return [
            'default' => 'Настройки групп',
            'all_chat_administrators' => 'Установка настроек группы',
        ];
    }
}
