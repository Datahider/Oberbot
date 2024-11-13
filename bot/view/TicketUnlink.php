<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\Oberbot\data\topic_user;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\view\AcceptingMessage;
use losthost\telle\Bot;

class TicketUnlink extends DBTracker {
    
    protected topic_user|topic_admin $object;
    protected ticket $ticket;
    
    public function track(\losthost\DB\DBEvent $event) {
        
        try { // чтоб не прерывать транзакцию если что
              // Делаем в INTRAN_DELETE, т.к. после объект уже unuseable
            $this->object = $event->object;
            $this->ticket = ticket::getById($this->object->topic_number);
            
            Service::message('info', Service::mentionById($this->object->user_id). Service::__(' покинул(а) заявку.'), null, $this->ticket->topic_id);
            $ticket_info = new AcceptingMessage($this->ticket);
            $ticket_info->show();
            
        } catch (\Exception $ex) {
            Bot::logException($ex);
        }
        
    }
}
