<?php

namespace losthost\Oberbot\controller\callback;

use losthost\telle\Bot;
use losthost\BotView\BotView;

class CallbackVerbose extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^verbose$/";
    const PERMIT = self::PERMIT_PRIVATE;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $callback_query->getFrom()->getId();
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('controllerCallbackVerbose', 'ctrlkbdCallbackVerbose', [], $callback_query->getMessage()->getMessageId());
        
        return true;
    }

}
