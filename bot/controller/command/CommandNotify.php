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
        
        $group_id = $message->getChat()->getId();
        $topic_id = $message->getMessageThreadId();
        
        if ($topic_id) {
            $ticket = ticket::getByGroupThread($group_id, $topic_id);
            $customers = $ticket->getCustomers();
        } else {
            $chat = new chat(['id' => $group_id]); 
            $customers = $chat->getCustomerIds();
        }   

        if (!$this->args) {
            $this->args = __('ознакомьтесь с последними сообщениями в этом чате.');
        }

        Bot::$api->deleteMessage($group_id, $message->getMessageId());
        sendMessage(mentionByIdArray($customers, __('Уважаемые пользователи'), true). ', '. $this->args, null, $group_id, $topic_id);
        
        return true;
    }
}
