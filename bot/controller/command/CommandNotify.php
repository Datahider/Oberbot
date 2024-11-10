<?php

namespace losthost\Oberbot\controller\command;

use losthost\DB\DBView;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\BotView\BotView;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionByIdArray;
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
            message('notification', mentionByIdArray($customers), __('<b>Ответьте</b>'), $topic_id);
        }
        
        return true;
    }
}
