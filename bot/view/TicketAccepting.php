<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\Oberbot\view\AcceptingMessage;

use function \losthost\Oberbot\mentionById;

class TicketAccepting extends DBTracker {
    
    public function track(\losthost\DB\DBEvent $event) {
        
        if (!is_array($event->data) || $event->data['function'] != 'accept') {
            return;
        }
        
        $ticket = $event->object;
        
        $group_id = $ticket->chat_id;
        $thread_id = $ticket->topic_id;
        $user_id = $ticket->ticket_creator;
        
        $accepting_message = new AcceptingMessage($ticket);
        $accepting_message->show();
        
    }
}
