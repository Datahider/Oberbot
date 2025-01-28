<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\DB\DBView;
use losthost\DB\DBValue;
use losthost\Oberbot\controller\action\ActionCreateTicket;
use losthost\ProxyMessage\Proxy;
use losthost\telle\Bot;

class CreateJobFromFunnel extends AbstractBackgroundProcess {
    
    public function run() {
        
        $task = new DBValue('SELECT * FROM fun_task_data WHERE id=?', [$this->param]);
        $messages = new DBView('SELECT * FROM fun_message_data WHERE task_id=? ORDER BY id');
        
        $ticket = ActionCreateTicket::do(null, $task->group_id, $task->user_id, $task->subject, []);
        
        $proxy = new Proxy(Bot::$api);
        foreach ($messages as $message_serialized) {
            $proxy->proxy(unserialize($message_serialized), $task->group_id, $ticket->topic_id);
        }
    }
}
