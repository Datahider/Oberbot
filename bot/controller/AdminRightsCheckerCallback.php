<?php

namespace losthost\Oberbot\controller;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\telle\Bot;
use losthost\BotView\BotView;

class AdminRightsCheckerCallback extends AbstractHandlerCallback {
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $member = Bot::$api->getChatMember($callback_query->getMessage()->getChat()->getId(), Bot::param('bot_userid', null));
        if ($member->getStatus() === 'administrator') {
            return false; // Ничего не обрабатываем, всё и так ок.
        }
        return true;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('controller_error_message', null, ['msg_text' => 'Дайте права админа!', 'value' => null], null, $callback_query->getMessage()->getMessageThreadId());
        return true;
    }
}
