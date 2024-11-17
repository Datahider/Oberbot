<?php

namespace losthost\Oberbot\controller\command;

use function \losthost\Oberbot\sendMessage;

use losthost\Oberbot\data\ticket;

class CommandArchive extends AbstractAuthCommand {
    
    const COMMAND = 'archive';
    
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        
        try {
            $ticket = ticket::getByGroupThread($group_id, $thread_id);
        } catch (\Exception $ex) {
            throw new \Exception(__('Эта команда предназначена для использования внутри заявки.'));
        }
        
        $ticket->archive();
        
        return true;
    }
}
