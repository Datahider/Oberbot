<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;

class CommandDone extends AbstractAuthCommand {
    
    const COMMAND = 'done';
    const DESCRIPTION = [
        'default' => 'Отметить заявку как завершенную',
        'all_group_chats' => 'Отметить заявку как завершенную'
    ];
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER; // Пользователи тоже могут закрывать заявки, почему бы и нет
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
        $ticket->close();

        return true;
    }
}
