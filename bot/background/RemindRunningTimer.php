<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\service\Service;

class RemindRunningTimer extends AbstractDisarmableBackgroundProcess {
    
    public function run() {

        $params = explode(' ', $this->param);
        $ticket_id = $params[0];
        $user = Service::getUserDataById($params[1]);
        
        $ticket = ticket::getById($ticket_id);
        
        $view = new BotView(Bot::$api, $user->id, $user->language_code);
        $view->show('backgroundRemindRunningTimer', null, ['ticket' => $ticket]);
    }
}
