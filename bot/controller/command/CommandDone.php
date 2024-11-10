<?php

namespace losthost\Oberbot\controller\command;

use losthost\DB\DBView;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\BotView\BotView;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionByIdArray;
use function \losthost\Oberbot\message;

class CommandDone extends AbstractAuthCommand {
    
    const COMMAND = 'done';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $topic_id = $message->getMessageThreadId();
        
        $ticket = ticket::getByGroupThread($group_id, $topic_id);
        $ticket->close();

        return true;
    }
}
