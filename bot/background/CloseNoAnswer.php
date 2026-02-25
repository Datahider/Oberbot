<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\DB\DB;
use losthost\telle\Bot;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\TicketUpdating;
use function \losthost\Oberbot\mentionByIdArray;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;


class CloseNoAnswer extends AbstractDisarmableBackgroundProcess {
    
    public function run() {
    
        $ticket = ticket::getById($this->param);

        Bot::$language_code = 'ru'; #TODO - Сделать получени кода языка из информации о чате.
        Bot::$api->sendMessage(
                $ticket->chat_id, 
                __('Заявка закрывается, т.к. от вас не был получен ответ.'), 
                'HTML', false, null, null, false, $ticket->topic_id);
        
        $ticket->close();
        
    }
}
