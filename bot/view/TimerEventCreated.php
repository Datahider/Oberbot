<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\Oberbot\view\AcceptingMessage;
use losthost\DB\DBEvent;
use losthost\timetracker\Timer;
use losthost\Oberbot\data\ticket;
use losthost\DB\DBValue;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\view\AgentsMessage;
use losthost\Oberbot\background\RemindRunningTimer;
use losthost\Oberbot\background\StopRunningTimer;

use function \losthost\Oberbot\mentionById;

class TimerEventCreated extends DBTracker {
    
    public function track(DBEvent $event) {
        
        $timer_event = $event->object;
        $timer = $this->getTimer($timer_event->timer);
        $user_id = $timer->subject;

        $ticket = ticket::getById($timer_event->object);
        
        if ($timer_event->started) {
            $agents_message = new AgentsMessage($ticket);
            $agents_message->show($user_id, AgentsMessage::ACTION_CONTINUE);
            
            $accepting_message = new AcceptingMessage($ticket);
            $accepting_message->show();
            
            Bot::runAt(new \DateTime("+25 minutes"), RemindRunningTimer::class, "$ticket->id $user_id");
            Bot::runAt(new \DateTime("+30 minutes"), StopRunningTimer::class, "$ticket->id $user_id");
        }
    }
    
    protected function getTimer(int $timer_id) : Timer {
        $timer_finder = new DBValue('SELECT subject FROM [timers] WHERE id = ?', [$timer_id]);
        return new Timer($timer_finder->subject);
    }
}
