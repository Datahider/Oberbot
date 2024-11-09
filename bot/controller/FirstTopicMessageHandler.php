<?php

namespace losthost\Oberbot\controller;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\BotView\BotView;

use function \losthost\Oberbot\isAgent;
use function \losthost\Oberbot\mentionById;

class FirstTopicMessageHandler extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        
        $topic_id = $message->getMessageThreadId();
        if (empty($topic_id)) {
            return false;
        }
        $group_id = $message->getChat()->getId();
       
        $ticket = ticket::getByGroupThread($group_id, $topic_id);
        if ($ticket->status == ticket::STATUS_CREATING) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $topic_id = $message->getMessageThreadId();
        $group_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        
        $ticket = ticket::getByGroupThread($group_id, $topic_id);
        if ($ticket->ticket_creator == $user_id) {
            $ticket->accept();
        } else {
            Bot::$api->deleteMessage($group_id, $message->getMessageId());
            $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
            $view->show('controller_error_message', null, ['msg_text' => 'Заявка в процессе создания.', 'value' => mentionById($user_id)], null, $message->getMessageThreadId());
        }
        
        return true;
    }
}
