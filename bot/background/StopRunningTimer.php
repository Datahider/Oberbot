<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;
use losthost\DB\DB;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\TimerEventUpdated;
use losthost\timetracker\TimerEvent;
use losthost\Oberbot\data\topic_admin;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\chat_settings;

class StopRunningTimer extends AbstractDisarmableBackgroundProcess {
    
    const NO_ACTIVITY = 'No AcTiViTy';
    
    public function run() {

        $params = explode(' ', $this->param);
        $ticket_id = $params[0];
        $user = Service::getUserDataById($params[1]);
        $ticket = ticket::getById($ticket_id);
        
        $settings = chat_settings::getChatSettinsByChatId($ticket->chat_id);
        if ($settings->pomodoro_like_timer) {
            // Обрабатываем как было задумано изначально
            // Проверим последнюю активность пользователя в тикете
            $ticket_admin = new topic_admin(['user_id' => $user->id, 'topic_number' => $ticket->id], true);
            if ($ticket_admin->isNew()                       // это уже не агент в этой заявке
                    || $ticket_admin->last_activity == null  // или он не обновлял время никогда
                    || $ticket_admin->last_activity->getTimestamp() < date_create()->getTimestamp()-300) { // или больше чем 5 минут назад

                $ticket->timerStop($user->id, self::NO_ACTIVITY);

            } else {
                $remind_after = $settings->remind_running_timer_minutes;
                $stop_after = $settings->stop_running_timer_minutes;
                Bot::runAt(new \DateTime("+$remind_after minutes"), RemindRunningTimer::class, "$ticket->id $user->id");
                Bot::runAt(new \DateTime("+$stop_after minutes"), StopRunningTimer::class, "$ticket->id $user->id");

                $view = new BotView(Bot::$api, $user->id, $user->language_code);
                $view->show('backgroundStopRunningTimer', null, ['ticket' => $ticket]);
            }
        } else {
            // Просто останавливаем таймер, т.к. пришло время.
            $ticket->timerStop($user->id, self::NO_ACTIVITY);
        }    
    }
}
