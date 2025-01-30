<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\DB\DBView;
use losthost\DB\DBValue;
use losthost\Oberbot\controller\action\ActionCreateTicket;
use losthost\ProxyMessage\Proxy;
use losthost\telle\Bot;
use TelegramBot\Api\BotApi;
use losthost\telle\model\DBUser;

class CreateJobFromFunnel extends AbstractBackgroundProcess {
    
    public function run() {
        
        $task = new DBValue('SELECT * FROM fun_task_data WHERE id=?', [$this->param]);
        $messages = new DBView('SELECT * FROM fun_message_data WHERE task_id=? ORDER BY id', [$this->param]);
        $bot = new DBValue('SELECT * FROM fun_bot_data WHERE tg_id=?', [$task->bot_id]);
        
        $ticket = ActionCreateTicket::do(null, $task->group_id, $task->user_id, $task->subject, []);
        
        $proxy = new Proxy(Bot::$api);
        $proxy->setAlternativeApi(new BotApi($bot->token));
        
        while ($messages->next()) {
            $message = unserialize($messages->message);
            $lang = $message->getFrom()->getLanguageCode();
            $user = $message->getFrom();
            $proxy->proxy($message, $task->group_id, $ticket->topic_id);
        }
        
        Bot::$language_code = $lang;
        Bot::$user = new DBUser($user);
        $ticket->toTicket();
    }
}
