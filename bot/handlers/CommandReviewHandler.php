<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\BotView\BotView;
use losthost\telle\Bot;

class CommandReviewHandler extends AbstractHandlerMessage {
    //put your code here
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $text = $message->getText();
        if (!$text) {
            return false;
        }
        
        if (preg_match("/^\/[Rr][Ee][Vv][Ii][Ee][Ww]$/", $text)) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $view = new BotView(Bot::$api, Bot::$chat->id);
        $view->show('cmd_review');
        return true;
    }
}
