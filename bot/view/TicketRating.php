<?php

namespace losthost\Oberbot\view;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\data\chat;

class TicketRating {
    
    protected ticket $ticket;
    
    public function __construct(ticket $ticket) {
        $this->ticket = $ticket;
    }
    
    public function show(?int $message_id=null) {
        
        $chat = chat::getById($this->ticket->chat_id); 
        $view = new BotView(Bot::$api, $chat->id, $chat->language_code);
        
        $view->show('viewRating', null, ['ticket' => $this->ticket], $message_id, $this->ticket->topic_id);
    }
}
