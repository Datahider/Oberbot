<?php

namespace losthost\Oberbot\view;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\data\chat;

class AgentsMessage {
    
    const ACTION_TAKE = 'Continue';
    const ACTION_PAUSE = 'Pause';
    const ACTION_CONTINUE = 'Continue';
    const ACTION_DONE = 'Done';
    const ACTION_ARCHIVE = 'Archive';

    protected $ticket;
    
    public function __construct(ticket $ticket) {
        $this->ticket = $ticket;
    }
    
    public function show(int $agent_id, string $action) {
        
        if (array_search($action, $allowed_actions) === false) {
            throw new \Exception('Inallowed action '. $action);
        }
        
        $chat = new chat(['id' => $this->ticket->chat_id], true);
        $view = new BotView(Bot::$api, $chat->id, $chat->language_code);
        
        $view->show('viewAgentsMessage'. $action, 'kbdAgentsMessage'. $action, ['ticket' => $this->ticket, 'user_id' => $agent_id], null, $this->ticket->topic_id);
    }
}
