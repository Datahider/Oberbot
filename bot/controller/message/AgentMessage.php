<?php

namespace losthost\Oberbot\controller\message;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\data\topic_admin;
use losthost\timetracker\TimerEvent;
use losthost\timetracker\Timer;
use losthost\Oberbot\data\chat_settings;
use losthost\Oberbot\background\StopRunningTimer;
use losthost\Oberbot\background\RemindRunningTimer;
use losthost\telle\Bot;

class AgentMessage extends AbstractMemberMessage {
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $from_id = $message->getFrom()->getId();
        $ticket = ticket::getByGroupThread($message->getChat()->getId(), $message->getMessageThreadId());
        $topic_admin = new topic_admin(['topic_number' => $ticket->id, 'user_id' => $from_id], true);
        
        if (!$topic_admin->isNew()) {       // такой админ(агент) в топике уже есть
            $timer = new Timer($from_id);
            if (!$timer->isStarted()) {     // Если таймер не запущен -- запускаем
                $ticket->timerStart($from_id);
            } else {
                $timer_event = new TimerEvent($timer, $timer->current_event);
                if ($timer_event->object != $ticket->id) { // Запускаем если сейчас он запущен в другом тикете
                    $ticket->timerStart($from_id);
                } else {
                    // Если работаем не по помидорам - нужно обновить таймауты
                    $settings = chat_settings::getChatSettinsByChatId($message->getChat()->getId());
                    if (!$settings->pomodoro_like_timer) {
                        $param = "$ticket->id $from_id";
                        RemindRunningTimer::disarm($param);
                        StopRunningTimer::disarm($param);
                        Bot::runAt(new \DateTime("+25 minutes"), RemindRunningTimer::class, $param);
                        Bot::runAt(new \DateTime("+30 minutes"), StopRunningTimer::class, $param);
                    }
                }
            }
            return true;
        }
        
        return false;
    }
}
