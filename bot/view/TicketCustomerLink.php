<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\view\AcceptingMessage;

class TicketCustomerLink extends DBTracker {
    
    public function track(\losthost\DB\DBEvent $event) {

        $topic_user = $event->object;
        $ticket = ticket::getById($topic_user->topic_number);
        
        if ($ticket->status != ticket::STATUS_CREATING) {
            $group_id = $ticket->chat_id;
            $thread_id = $ticket->topic_id;
            $user_id = $topic_user->user_id;

            Service::message('info', Service::mentionById($user_id). ', '. Service::__('вы присоединились к заявке.'), null, $thread_id);
            $ticket_info = new AcceptingMessage($ticket);
            $ticket_info->show();
        }
    }
}
