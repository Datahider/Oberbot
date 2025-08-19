<?php

namespace losthost\Oberbot\controller\callback\settings;

use losthost\Oberbot\controller\callback\AbstractCallback;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\chat_settings;

class CallbackSettingsModify extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^settings_(\d+)$/";
    const PERMIT = self::PERMIT_PRIVATE;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
    
        $settings = new chat_settings(['id' => $this->matches[1]]); 
        $view = new BotView(Bot::$api, Bot::$chat->id);
        $view->show('SettingsView', null, ['settings' => $settings], $callback_query->getMessage()->getMessageId());
        
        return true;
    }
}
