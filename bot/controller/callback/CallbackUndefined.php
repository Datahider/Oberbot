<?php

namespace losthost\Oberbot\controller\callback;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\service\Service;

class CallbackUndefined extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/.*/";
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        return true;
    }
}
