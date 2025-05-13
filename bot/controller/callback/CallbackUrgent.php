<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;

class CallbackUrgent extends AbstractCallback {

    const CALLBACK_DATA_PATTERN = "/^(urgent)$/";
    const PERMIT = self::PERMIT_MANAGER;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {

        $ticket = ticket::getByGroupThread(
                $callback_query->getMessage()->getChat()->getId(), 
                $callback_query->getMessage()->getMessageThreadId());
        
        $ticket->setType(ticket::TYPE_PRIORITY_TASK);
        try {
            Bot::$api->editMessageReplyMarkup(Bot::$chat->id, $callback_query->getMessage()->getMessageId());
        } catch (Exception $ex) {
            Bot::logException($ex);
        }
        
        return true;
    }
}
