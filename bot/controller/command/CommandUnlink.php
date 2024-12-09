<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandUnlink extends AbstractAuthCommand {
    
    const COMMAND = 'unlink';
    const DESCRIPTION = [
        'default' => '«Отключение» от заявки',
        'all_group_chats' => '«Отключение» от заявки'
    ];
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
        $ticket->unlink($this->user_id);
        
        return true;
    }
}
