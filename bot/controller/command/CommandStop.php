<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\chat;
use losthost\Oberbot\service\Service;
use losthost\telle\Bot;

class CommandStop extends AbstractAuthCommand {
    
    const COMMAND = "stop";
    const PERMIT = self::PERMIT_ADMIN;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getMessageThreadId() > 1) {
            Service::message('warning', 'Команда /stop предназначена для использования в общем чате.', null, $message->getMessageThreadId());
        } else {
            $chat = new chat(['id' => $message->getChat()->getId()], true);
            $chat->process_tickets = false;
            $chat->language_code = Bot::$language_code;
            $chat->write();
        }
        
        return true;
    }
}
