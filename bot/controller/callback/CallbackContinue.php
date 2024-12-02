<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\Oberbot\service\Service;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\mentionById;

class CallbackContinue extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^continue$/";
    const PERMIT = self::PERMIT_AGENT;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {

        $group_id = $callback_query->getMessage()->getChat()->getId();
        $thread_id = $callback_query->getMessage()->getMessageThreadId();
        $user_id = $callback_query->getFrom()->getId();
        $message_id = $callback_query->getMessage()->getMessageId();
        
        Bot::$api->editMessageReplyMarkup($group_id, $message_id);

        $ticket = ticket::getByGroupThread($group_id, $thread_id);

        if ($ticket->hasAgent($user_id)) {
            $ticket->timerStart($user_id);
        } else {
            sendMessage(__('%mention%, вы не связаны с этой заявкой. Испольуйте команду /take.', ['mention' => mentionById($user_id)]), null, null, $thread_id);
        }
        
        return true;
    }
}
