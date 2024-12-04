<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\session;
use losthost\telle\Bot;
use losthost\Oberbot\controller\action\ActionActiveListDisplay;

use function \losthost\Oberbot\__;

class CallbackList extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^list_(.+)$/";
    const PERMIT = self::PERMIT_PRIVATE;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $session = new session(['user_id' => Bot::$user->id, 'chat_id' => Bot::$chat->id], true);
        $session->working_group = $this->matches[1];
        $session->write();
        
        ActionActiveListDisplay::do(Bot::$user->id, Bot::$chat->id, $callback_query->getMessage()->getMessageId());
        return true;
    }
}
