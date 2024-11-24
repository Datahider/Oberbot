<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;

class CommandCreate extends AbstractAuthCommand {
    
    const COMMAND = 'create';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        try {
            ticket::getByGroupThread($this->chat_id, $this->thread_id);
            Service::message('warning', "Заявка связанная с этим топиком уже существует.", null, $this->thread_id);
        } catch (\Exception $ex) {
            $ticket = ticket::create($this->chat_id, $this->thread_id, $this->args, $this->user_id);
            $ticket->accept();
        }
        
        return true;
    }
}
