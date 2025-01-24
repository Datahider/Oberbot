<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\session;
use losthost\telle\Bot;
use losthost\Oberbot\controller\display\ActiveListDisplay;

use function \losthost\Oberbot\__;

class CallbackList extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^list_(.*)$/";
    const PERMIT = self::PERMIT_PRIVATE;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $session = new session(['user_id' => Bot::$user->id, 'chat_id' => Bot::$chat->id], true);
        $session->working_group = empty($this->matches[1]) ? null : $this->matches[1];
        $session->write();
        
        ActiveListDisplay::display(Bot::$user->id, Bot::$chat->id, $callback_query->getMessage()->getMessageId());
        return true;
    }
}
