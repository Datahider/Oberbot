<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\DB\DB;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\TicketClosing;

class CloseIncompleteTicket extends AbstractBackgroundProcess {
    
    public function run() {
    
        DB::addTracker(DBEvent::AFTER_UPDATE, ticket::class, TicketClosing::class);
        
        $ticket = ticket::getById($this->param);
        $ticket->close();
        
    }
}
