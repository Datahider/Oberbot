<?php

namespace losthost\Oberbot\controller;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\BotView\BotView;

class AdminRightsCheckerMessage extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $member = Bot::$api->getChatMember($message->getChat()->getId(), Bot::param('bot_userid', null));
        if ($member->getStatus() === 'administrator') {
            return false; // Ничего не обрабатываем, всё и так ок.
        }
        return true;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('controller_error_message', null, ['msg_text' => 'Дайте права админа!', 'value' => null], null, $message->getMessageThreadId());
        return true;
    }
}
