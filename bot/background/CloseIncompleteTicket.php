<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\DB\DB;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\TicketUpdating;

class CloseIncompleteTicket extends AbstractBackgroundProcess {
    
    public function run() {
    
        $ticket = ticket::getById($this->param);
        $ticket->close();
        
    }
}
