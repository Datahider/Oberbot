<?php

namespace losthost\Oberbot\controller\callback\settings;

use losthost\Oberbot\controller\callback\AbstractCallback;

class CallbackSettingsCreate extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^settings_create$/";
    const PERMIT = self::PERMIT_PRIVATE;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
    }
}
