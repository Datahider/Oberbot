<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\DB\DB;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\TicketUpdating;

class CloseNoAnswer extends AbstractDisarmableBackgroundProcess {
    
    public function run() {
    
        $ticket = ticket::getById($this->param);

        sendMessage(__('Заявка закрывается, т.к. от вас не был получен ответ.'), null, $ticket->chat_id, $ticket->topic_id);
        
        $ticket->close();
        
    }
}
