<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\service\Service;
use losthost\BotView\BotView;
use losthost\telle\Bot;
use losthost\Oberbot\data\ticket;
use losthost\DB\DBValue;

class RemindTicket extends AbstractBackgroundProcess {
    
    protected $user;
    protected $ticket;
    
    public function run() {
    
        $args = explode(" ", $this->param);
        $this->user = $this->getUserById($args[0]);
        $this->ticket = ticket::getById($args[1]);
        
        if (Service::isUsersChatAccessible($this->user->id)) {
            $this->remindInPrivate();
        } else {
            $this->remindInTicket();
        }
        
    }
    
    protected function getUserById(int $user_id) {
        return new DBValue('SELECT * FROM [telle_users] WHERE id = ?', [$user_id]);
    }
    
    protected function remindInPrivate() {
        $view = new BotView(Bot::$api, $this->user->id, $this->user->language_code);
        $view->show('backgroundRemindPrivate', null, ['ticket' => $this->ticket, 'user' => $this->user]);
    }
    
    protected function remindInTicket() {
        $view = new BotView(Bot::$api, $this->user->id, $this->user->language_code);
        $view->show('backgroundRemindTicket', null, ['ticket' => $this->ticket, 'user' => $this->user], null, $this->ticket->topic_id);
    }
    
}
