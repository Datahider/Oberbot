<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\Oberbot\service\Service;

use function \losthost\Oberbot\__;

class CallbackPause extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^pause(_(\d+))?$/";
    const PERMIT = self::PERMIT_AGENT;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {

        $group_id = $callback_query->getMessage()->getChat()->getId();
        $thread_id = $callback_query->getMessage()->getMessageThreadId();
        $user_id = $callback_query->getFrom()->getId();
        $message_id = $callback_query->getMessage()->getMessageId();
        
        if ($this->matches[2] && $user_id != $this->matches[2]) {
            return __('Это таймер другого агента.');
        }
        Bot::$api->editMessageReplyMarkup($group_id, $message_id);

        $ticket = ticket::getByGroupThread($group_id, $thread_id);
        $ticket->touchAdmin($user_id);
        $ticket->timerStop($user_id);
        
        Service::showNextTicket($user_id);
        return true;
    }
}
