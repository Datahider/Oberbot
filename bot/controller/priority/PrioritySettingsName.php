<?php

namespace losthost\Oberbot\controller\priority;

use losthost\telle\abst\AbstractHandlerMessage;

class PrioritySettingsName extends AbstractHandlerPriority {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        return $message->getText();
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        
    }

    public function getPrompt(): string {
        return "<b>Создание набора настроек</b>\n\nВведите название набора настроек";
    }
}
