<?php

namespace losthost\Oberbot\controller\callback;

use losthost\telle\Bot;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionById;

class CallbackCancelUrgent extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^cancel_urgent$/";
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
    
        $message_id = $callback_query->getMessage()->getMessageId();
        $group_id = $callback_query->getMessage()->getChat()->getId();
        $thread_id = $callback_query->getMessage()->getMessageThreadId();
        
        try {
            Bot::$api->editMessageReplyMarkup($group_id, $message_id);
        } catch (Exception $ex) {
            Bot::logException($ex);
        }
        
        sendMessage(__('Отменено %mention%', ['mention' => mentionById(Bot::$user->id, true)]), null, $group_id, $thread_id);
        return true;
    }
}
