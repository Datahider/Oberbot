<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\timetracker\Timer;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\session;
use losthost\DB\DBView;

class TimerStopped extends DBTracker {
    
    protected Timer $timer;
    
    public function track(\losthost\DB\DBEvent $event) {
    
        $this->timer = $event->object;
        
        if (array_search('current_event', $event->fields) === false) {
            return;
        }
        
        if (!$this->timer->isStarted()) {
            $session = new session(['user_id' => $this->timer->subject, 'chat_id' => $this->timer->subject], true);
            $user = new DBView('SELECT language_code FROM [telle_users] WHERE id=?', [$session->user_id]);
            if ($user->next()) {
                $ticket = Service::getOldestTicket($session->user_id, $session->working_group);

                $view = new BotView(Bot::$api, $session->chat_id, $user->language_code);
                $view->show('controllerCommandNext', null, ['ticket' => $ticket, 'working_group' => $session->working_group]);
            }
        }
    }
}
