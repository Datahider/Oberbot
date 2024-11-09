<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\message;
use function \losthost\Oberbot\mentionById;

class TicketCreating extends DBTracker {
    
    public function track(\losthost\DB\DBEvent $event) {
        $ticket = $event->object;
        
        $group_id = $ticket->chat_id;
        $thread_id = $ticket->topic_id;
        $user_id = $ticket->ticket_creator;
        
        message('tip', mentionById($user_id). ', '. __('пожалуйста, подробно опишите суть вашего вопроса.'), null, $thread_id);
    }
}
