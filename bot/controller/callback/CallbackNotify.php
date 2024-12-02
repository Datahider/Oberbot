<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\Oberbot\service\Service;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\mentionByIdArray;
use function \losthost\Oberbot\__;

class CallbackNotify extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^notify(_(\d+))?$/";
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

        $customers = $ticket->getCustomers();
        if (!empty($customers)) {
            $ticket->awaitUser();
            sendMessage(__('%mentions%, работа над заявкой приостановлена до получения ответа.', [ 'mentions' => mentionByIdArray($customers)]), null, $group_id, $thread_id);
        } else {
            sendMessage(__('К тикету не привязан ни один пользователь.'), null, $group_id, $thread_id);
        }
        
        $ticket->touchAdmin($user_id);
        $ticket->timerStop($user_id);
        
        Service::showNextTicket($user_id);
        return true;

    }
}
