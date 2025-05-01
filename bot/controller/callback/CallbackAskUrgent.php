<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\chat;

use function \losthost\Oberbot\sendMessage;
use losthost\telle\Bot;
use losthost\BotView\BotView;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionByIdArray;

class CallbackAskUrgent extends AbstractCallback {

    const CALLBACK_DATA_PATTERN = "/^ask_urgent$/";
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $group_id = $callback_query->getMessage()->getChat()->getId();
        $thread_id = $callback_query->getMessage()->getMessageThreadId();
        
        $group = chat::getById($group_id);
        $managers = $group->getManagerIds();
        
        
        $view = new BotView(Bot::$api, $group_id, Bot::$language_code);
        $view->show('controllerCallbackAskUrgent', 'ctrlkbdCallbackAskUrgent', ['managers' => $managers, 'group' => $group, 'user_id' => Bot::$user->id], null, $thread_id);
        
        return true;
    }
}
