<?php

namespace losthost\Oberbot\controller\command;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\session;
use losthost\Oberbot\data\user_chat_role;
use losthost\DB\DBList;
use losthost\Oberbot\data\ticket;
use losthost\DB\DBView;
use losthost\telle\Bot;
use losthost\BotView\BotView;

class CommandNext extends AbstractHandlerCommand {
    
    const COMMAND = 'next';

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $user_id = $message->getFrom()->getId();
        $chat_id = $message->getChat()->getId();
        
        if ($user_id != $chat_id) {
            Service::message('warning', 'Команда /next предназначена для личного чата с ботом.', null, $message->getMessageThreadId());
            return true;
        }

        
        if ($this->args) {
            $session = new session(['user_id' => $user_id, 'chat_id' => $chat_id], true);
            $session->working_group = $this->args == 'all' ? null : $this->args;
            $session->isModified() && $session->write();
        }
        
        Service::showNextTicket($user_id);
        return true;
    }
        
}
