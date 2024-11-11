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

use function \losthost\Oberbot\mentionById;

class TimerEventCreated extends DBTracker {
    
    public function track(DBEvent $event) {
        
        $timer_event = $event->object;
        $timer = $this->getTimer($timer_event->timer);
        $user_id = $timer->subject;

        $ticket = ticket::getById($timer_event->object);
        $group_id = $ticket->chat_id;
        $thread_id = $ticket->topic_id;
        $chat = new chat(['id' => $group_id]);
        
        if ($timer_event->started) {
            $mention = mentionById($user_id);
            $view = new BotView(Bot::$api, $chat->id, $chat->language_code);
            $view->show('viewTimerEventCreated', null, ['mention' => $mention], null, $thread_id);
            
            $accepting_message = new AcceptingMessage($ticket);
            $accepting_message->show();
        }
    }
    
    protected function getTimer(int $timer_id) : Timer {
        $timer_finder = new DBValue('SELECT subject FROM [timers] WHERE id = ?', [$timer_id]);
        return new Timer($timer_finder->subject);
    }
}