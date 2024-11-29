<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\action\ActionCreateTicket;
use losthost\telle\Bot;

class CommandSub extends AbstractAuthCommand {
    
    const COMMAND = 'sub';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $this->check2($message);
        
        $title = $this->getTitle($message);
        $messages = $this->getMessages($message);
        
        $ticket = ActionCreateTicket::do(Bot::$chat->id, Bot::$chat->id, $message->getFrom()->getId(), $title, $messages);
        
        return true;
    }
    
    protected function check2(\TelegramBot\Api\Types\Message &$message) {
        throw new \Exception('Not implemented yet');
    }
    
    protected function getTitle(\TelegramBot\Api\Types\Message &$message) : string {
        $result = '';
        throw new \Exception('Not implemented yet');
        return $result;
    }
    
    protected function getMessages(\TelegramBot\Api\Types\Message &$message) : array {
        $result = [];
        throw new \Exception('Not implemented yet');
        return $result;
    }
}
