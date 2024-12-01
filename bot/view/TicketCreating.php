<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\user_meta;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\data\private_topic;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class TicketCreating extends DBTracker {
    
    protected ticket $ticket;
    
    public function track(\losthost\DB\DBEvent $event) {
        $this->ticket = $event->object;
        
        $group_id = $this->ticket->chat_id;
        $thread_id = $this->ticket->topic_id;
        $user_id = $this->ticket->ticket_creator;
        
        if (user_meta::get($user_id, 'TicketCreatingTip', 'on') == 'on') {
            $view = new BotView(Bot::$api, $group_id, Bot::$language_code);
            $view->show('viewTicketCreatingTip', 'kbdTicketCreatingTip', ['user_id' => $user_id], null, $thread_id);
        }
        
    }
    
}
