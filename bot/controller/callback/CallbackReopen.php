<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\view\AgentsMessage;

class CallbackReopen extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^reopen$/";
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {

        $group_id = $callback_query->getMessage()->getChat()->getId();
        $thread_id = $callback_query->getMessage()->getMessageThreadId();
        $message_id = $callback_query->getMessage()->getMessageId();
        
        Bot::$api->editMessageReplyMarkup($group_id, $message_id);

        $ticket = ticket::getByGroupThread($group_id, $thread_id);
        $ticket->reopen();
        
        return true;
    }
}
