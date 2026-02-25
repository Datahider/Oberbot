<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\DB\DB;
use losthost\telle\Bot;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\TicketUpdating;

class CloseNoAnswer extends AbstractDisarmableBackgroundProcess {
    
    public function run() {
    
        $ticket = ticket::getById($this->param);

        Bot::$api->sendMessage(
                $ticket->chat_id, 
                __('Заявка закрывается, т.к. от вас не был получен ответ.'), 
                'HTML', false, null, null, false, $ticket->topic_id);
        
        $ticket->close();
        
    }
}
