<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

use function \losthost\Oberbot\message;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionById;

class CommandContinue extends AbstractAuthCommand {
    
    const COMMAND = 'continue';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);

        if ($ticket->hasAgent($this->user_id)) {
            $ticket->timerStart($this->user_id);
        } else {
            message('warning', sprintf(__('%s, вы не связаны с этой заявкой. Испольуйте команду /take.'), mentionById($this->user_id)), null, $this->thread_id);
        }
        
        return true;
    }
}
