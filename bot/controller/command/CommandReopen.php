<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandReopen extends AbstractAuthCommand {
    
    const COMMAND = 'reopen';
    const DESCRIPTION = [
        'default' => 'Переотрытие закрытой заявки',
        'all_group_chats' => 'Переоткрытие закрытой заявки'
    ];
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
        $ticket->reopen();
        
        return true;
    }
}
