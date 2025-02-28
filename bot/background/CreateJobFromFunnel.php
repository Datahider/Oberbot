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
use losthost\telle\model\DBChat;
use losthost\Oberbot\data\ticket;
use losthost\BotView\BotView;

class CreateJobFromFunnel extends AbstractBackgroundProcess {
    
    public function run() {
        
        $task = new DBValue('SELECT * FROM fun_task_data WHERE id=?', [$this->param]);
        $messages = new DBView('SELECT * FROM fun_message_data WHERE task_id=? ORDER BY id', [$this->param]);
        $bot = new DBValue('SELECT * FROM fun_bot_data WHERE tg_id=?', [$task->bot_id]);
        
        $alternative_api = new BotApi($bot->token);
        $bot_data = $alternative_api->getMe();
        
        // TODO - сделать нормальное получение языка
        Bot::$language_code = $bot_data->getLanguageCode() ? $bot_data->getLanguageCode() : 'ru';
        
        $ticket = ActionCreateTicket::do(null, $task->group_id, $task->user_id, $task->subject, []);
        
        $proxy = new Proxy(Bot::$api);
        $proxy->setAlternativeApi($alternative_api);
        
        while ($messages->next()) {
            $message = unserialize($messages->message);
            $lang = $message->getFrom()->getLanguageCode();
            $user = $message->getFrom();
            $chat = $message->getChat();
            $proxy->proxy($message, $task->group_id, $ticket->topic_id);
        }
        
        Bot::$user = new DBUser($user);
        Bot::$chat = new DBChat($chat);
        Bot::$language_code = $lang;
        
        $this->notifyAgents($ticket);
    }
    
    protected function notifyAgents(ticket $ticket) {
        
        // TODO - доставать так же language_code для агента
        $agent = new DBView('SELECT user_id AS id FROM [user_chat_role] WHERE role = "agent" AND chat_id = ?', [$ticket->chat_id]);
        
        while ($agent->next()) {
            $view = new BotView(Bot::$api, $agent->id);
            $view->show('privateFunnelNotification', null, ['ticket' => $ticket]);
        }
        
        
    }
}
