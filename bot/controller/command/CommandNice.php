<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\data\ticket;

use function \losthost\Oberbot\sendMessage;

class CommandNice extends AbstractAuthCommand {
    
    const COMMAND = 'nice';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        if ($this->thread_id > 1) {
            $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
            if ($this->args != '') {
                $ticket->nice = $this->args;
                $ticket->isModified() && $ticket->write();
                $ticket->fetch();
            }
            sendMessage($ticket->nice, null, $this->chat_id, $this->thread_id);
        } else {
            $chat = chat::getById($this->chat_id);
            if ($this->args != '') {
                $chat->nice = $this->args;
                $chat->isModified() && $chat->write();
                $chat->fetch();
            }
            sendMessage($chat->nice, null, $this->chat_id);
        }
        
        return true;
    }

    protected static function permit(): int {
        return self::PERMIT_AGENT;
    }

    public static function description(): array {
        'Точная настройка приоритета заявки или группы';
    }
}
