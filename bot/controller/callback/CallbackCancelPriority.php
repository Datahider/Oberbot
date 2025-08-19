<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\controller\priority\AbstractHandlerPriority;
use losthost\telle\Bot;

class CallbackCancelPriority extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^cancel_priority_?(.*)/";
    const PERMIT = self::PERMIT_PRIVATE;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        AbstractHandlerPriority::unsetPriority();
        Bot::$api->deleteMessage(Bot::$chat->id, $callback_query->getMessage()->getMessageId());
        return true;
    }
}
