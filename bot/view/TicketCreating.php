<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\user_meta;

class TicketCreating extends DBTracker {
    
    public function track(\losthost\DB\DBEvent $event) {
        $ticket = $event->object;
        
        $group_id = $ticket->chat_id;
        $thread_id = $ticket->topic_id;
        $user_id = $ticket->ticket_creator;
        
        if (user_meta::get($user_id, 'TicketCreatingTip', 'on') == 'on') {
            $view = new BotView(Bot::$api, $group_id, Bot::$language_code);
            $view->show('viewTicketCreatingTip', 'kbdTicketCreatingTip', ['user_id' => $user_id], null, $thread_id);
        }
    }
}
