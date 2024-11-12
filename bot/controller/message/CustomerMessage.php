<?php

namespace losthost\Oberbot\controller\message;

use losthost\Oberbot\service\Service;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\ticket;

class CustomerMessage extends AbstractMemberMessage {
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        if (Service::isAgent($user_id, $group_id)) {
            return false;
        }
        
        $ticket = ticket::getByGroupThread($group_id, $thread_id);
        
        $view = new BotView(Bot::$api, $group_id, Bot::$language_code);
        
        if ($ticket->status == ticket::STATUS_CLOSED) {
            $view->show('controllerCustomerMessageClosed', 'ctrlkbdCustomerMessageClosed', [], null, $thread_id);
        }
        
        return true;
    }
}
