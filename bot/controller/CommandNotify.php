<?php

namespace losthost\Oberbot\controller;

use losthost\DB\DBView;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\BotView\BotView;

use function __;
use function \losthost\Oberbot\mentionById;
use function \losthost\Oberbot\message;

class CommandNotify extends AbstractAuthCommand {
    
    const COMMAND = 'notify';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $topic_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        $ticket = ticket::getByGroupThread($group_id, $topic_id);
        $customers = $ticket->getCustomers();
        
        if (!empty($customers)) {
            $text = '';
            foreach ($customers as $customer) {
                $text .= ', '. mentionById($customer);
            }
            
            message('info', substr($text, 2), null, $topic_id);
        }
        
        return true;
    }
}
