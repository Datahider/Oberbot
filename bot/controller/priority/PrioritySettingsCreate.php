<?php

namespace losthost\Oberbot\controller\priority;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\service\ExtendedInlineKeyboardMarkup;
use losthost\telle\Bot;
use losthost\Oberbot\data\chat_settings;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class PrioritySettingsCreate extends AbstractHandlerPriority {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        return $message->getText();
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
     
        $name = $message->getText();
        if (mb_strlen($name) > 16) {
            sendMessage(__('settings_max_len=16'));
        } else {
            $settings = new chat_settings([], true);
            $settings->name = $name;
            $settings->owner_id = Bot::$user->id;
            $settings->write();
            
            sendMessage(__('new_settings_{{name}}_created', [
                'name' => $settings->name,
                
            ]));
            $this->unsetPriority();
        }
        
        return true;
    }

    public function getPrompt(): string {
        return "<b>Создание набора настроек</b>\n\nВведите название набора настроек";
    }

    protected function getInlineKeyboard(): \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup {
        return ExtendedInlineKeyboardMarkup::fromLinearArray(['cancel_priority_settings_creation']);
    }
}
