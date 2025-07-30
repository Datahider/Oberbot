<?php

namespace losthost\Oberbot\controller\callback;

use losthost\telle\Bot;
use function \losthost\Oberbot\__;

class CallbackLeaveChat extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^leave_chat$/";
    const PERMIT = self::PERMIT_USER;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        Bot::$api->banChatMember(Bot::$chat->id, Bot::$user->id);
        if ($callback_query->getMessage()->getChat()->getType() == 'supergroup') {
            Bot::$api->unbanChatMember(Bot::$chat->id, Bot::$user->id);
        }
        
        return __('Вы были исключены из группы.');
        
    }
}
