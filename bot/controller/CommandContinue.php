<?php

namespace losthost\Oberbot\controller;

use losthost\Oberbot\controller\AbstractAuthCommand;
use losthost\Oberbot\data\ticket;

use function \losthost\Oberbot\message;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionById;

class CommandContinue extends AbstractAuthCommand {
    
    const COMMAND = 'continue';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        $ticket = ticket::getByGroupThread($group_id, $thread_id);

        if ($ticket->hasAgent($user_id)) {
            $ticket->timerStart($user_id);
        } else {
            message('warning', sprintf(__('%s, вы не связаны с этой заявкой. Испольуйте команду /take.'), mentionById($user_id)), null, $thread_id);
        }
        
        return true;
    }
}
