<?php

namespace losthost\Oberbot\controller;

use losthost\telle\Bot;
use losthost\BotView\BotView;

class CommandStart extends AbstractAuthCommand {
    
    const COMMAND = 'start';
    const PERMIT = self::PERMIT_PRIVATE;

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('controllerCommandStart', 'controllerKeyboardCommandStart');
        return true;
    }
    
}
