<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;
use losthost\DB\DB;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\TimerEventUpdated;
use losthost\timetracker\TimerEvent;

class StopRunningTimer extends AbstractDisarmableBackgroundProcess {
    
    public function run() {

        $params = explode(' ', $this->param);
        $ticket_id = $params[0];
        $user = Service::getUserDataById($params[1]);
        $ticket = ticket::getById($ticket_id);
        
        // надо подключить трекер, чтоб сработало уведомление об остановке таймера
        DB::addTracker(DBEvent::AFTER_UPDATE, TimerEvent::class, TimerEventUpdated::class);
        
        $ticket->timerStop($user->id);
    }
}
