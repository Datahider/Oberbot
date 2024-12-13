<?php

namespace losthost\Oberbot\controller\command;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\Oberbot\service\Service;

use function \losthost\Oberbot\isAgent;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;

class CommandDigits extends AbstractHandlerMessage {
    
    protected array $m;
    protected $ticket;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $this->m = [];
        
        if (!isAgent(Bot::$user->id, Bot::$chat->id)) {
            return false;
        }
        
        if (preg_match("/^\/(\d+)$/", $message->getText(), $this->m)) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $this->ticket = ticket::getByGroupThread($group_id, $thread_id);
        
        if ($this->m[1] >= 10000) {
            $this->waitTicket();
        } else {
            $this->waitTime();
        }
        
        if ($this->ticket->isTimerStarted(Bot::$user->id)) {
            $this->ticket->timerStop(Bot::$user->id);
        }
        Service::showNextTicket(Bot::$user->id);
        
        return true;
    }
    
    protected function waitTicket() {
        if (!$this->ticket->waitTask($this->m[1])) {
            sendMessage(__("Не найден тикет #%ticket%", ['ticket' => $this->m[1]]), null, $this->ticket->chat_id, $this->ticket->topic_id);
        }
    }
    
    protected function waitTime() {
        $minutes = $this->m[1];
        $till = date_create("+$minutes min");
        $user_id = Bot::$user->id;
        $ticket_id = $this->ticket->id;
        
        Bot::runAt($till, RemindTicket::class, "$user_id $ticket_id");        
        $this->ticket->waitTime($till);
    }
}
