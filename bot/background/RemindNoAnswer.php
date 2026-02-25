<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\DB\DB;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\TicketUpdating;
use function \losthost\Oberbot\mentionByIdArray;

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
        
        sendMessage(__(<<<FIN
                %mentions%, напоминаю, что работы по заявке не ведутся, т.к. от вас не получен ответ.
                    
                Если ответ не будет получен, %days% заявка будет закрыта.
                FIN, $message_params), null, $ticket->chat_id, $ticket->topic_id);
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
