<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;

class CommandCreate extends AbstractAuthCommand {
    
    const COMMAND = 'create';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        try {
            ticket::getByGroupThread($group_id, $thread_id);
            Service::message('warning', "Заявка связанная с этим топиком уже существует.", null, $thread_id);
        } catch (\Exception $ex) {
            $ticket = ticket::create($group_id, $thread_id, $this->args, $user_id);
            $ticket->accept();
        }
        
        return true;
    }
}
