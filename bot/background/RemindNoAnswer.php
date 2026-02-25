<?php

namespace losthost\Oberbot\background;

use losthost\telle\Bot;
use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\DB\DB;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\TicketUpdating;
use function \losthost\Oberbot\mentionByIdArray;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class RemindNoAnswer extends AbstractDisarmableBackgroundProcess {
    
    #[\Override]
    public function run() {
    
        // Параметры: 
        //      ticket_id - идентификатор заявки
        //      days      - количество дней оставшихся до закрытия заявки
        
        $params = explode(' ', $this->param);
        $ticket_id = $params[0];
        $days = $params[1];
        
        $ticket = ticket::getById($ticket_id);
        
        $message_params = [
            'mentions' => mentionByIdArray($ticket->getCustomers()),
            'days' => $this->daysText($days)
        ];
        
        Bot::$language_code = 'ru'; #TODO - Сделать получени кода языка из информации о чате.
        Bot::$api->sendMessage($ticket->chat_id, __(<<<FIN
                %mentions%, напоминаю, что работы по заявке не ведутся, т.к. от вас не получен ответ.
                    
                Если ответ не будет получен, %days% заявка будет закрыта.
                FIN, $message_params), 'HTML', false, null, null, false, $ticket->topic_id);
    }
                
    protected function daysText(int $days) {
        
        switch ($days) {
            case 1:
                return 'завтра';
            case 2:
            case 3:
            case 4:
                return "через $days дня";
        }
        return "через $days дней";
    }
}
