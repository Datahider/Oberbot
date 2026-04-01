<?php

namespace losthost\Oberbot\background;

use losthost\Oberbot\data\ticket;
use losthost\DB\DB;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\TicketUpdating;
use losthost\Oberbot\background\AbstractDisarmableBackgroundProcess;

class CloseIncompleteTicket extends AbstractDisarmableBackgroundProcess {
    
    public function run() {
    
        $ticket = ticket::getById($this->param);
        $ticket->close();
        
    }
}
