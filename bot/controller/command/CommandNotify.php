<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;
use losthost\telle\Bot;
use losthost\Oberbot\data\chat;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\mentionByIdArray;
use function \losthost\Oberbot\__;

class CommandNotify extends AbstractAuthCommand {
    
    const COMMAND = 'notify';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        if ($topic_id) {
            $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
            $customers = $ticket->getCustomers();
        } else {
            $chat = new chat(['id' => $this->chat_id]); 
            $customers = $chat->getCustomerIds();
        }   

        if (!$this->args) {
            $this->args = __('ознакомьтесь с последними сообщениями в этом чате.');
        }

        Bot::$api->deleteMessage($this->chat_id, $message->getMessageId());
        sendMessage(mentionByIdArray($customers, __('Уважаемые пользователи'), true). ', '. $this->args, null, $this->chat_id, $this->thread_id);
        
        return true;
    }
}
