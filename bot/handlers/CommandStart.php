<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\telle\Bot;
use losthost\BotView\BotView;

class CommandStart extends AbstractHandlerCommand {
    
    const COMMAND = 'start';

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        if ($message->getChat()->getId() === $message->getFrom()->getId()) {
            $this->processInPrivate($this->args);
        } else {
            $this->processInGroup($this->args);
        }
        
        return true;
    }
    
    public function processInPrivate(string $args) {
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('cmd_start_in_private');
    }
    
    public function processInGroup(string $args) {
        
    }
}
