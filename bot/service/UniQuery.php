<?php

namespace losthost\Oberbot\service;

use losthost\ReflexA\Mind\UserQuery;
use losthost\ReflexA\Data\UserData;
use losthost\ReflexA\Data\Prompt;
use losthost\ReflexA\ReflexA;
use losthost\DB\DBView;

class UniQuery extends UserQuery {

    protected string $name;
    
    public function __construct(int $user_id, $agent_name=null) {
        parent::__construct($user_id);
        $this->name = $agent_name ?? 'uni';
    }
    protected function agentName(): string {
        return $this->name;
    }
    
    protected function makeContext(): array {
        $user_data = new UserData(['id' => $this->user_id], true);
        
        if ($user_data->prompt_id) {
            $prompt = new Prompt(['id' => $user_data->prompt_id, 'user' => $this->user_id]);
        } else {
            $prompt = new Prompt();
            $prompt->user = $this->user_id;
            $prompt->prompt = ReflexA::getConfig($this->agentName(), 'prompt');
            $prompt->date_time = date_create();
            $prompt->write();
            $user_data->prompt_id = $prompt->id;
            $user_data->context_start = date_create();
            $user_data->write();
        }
        
        $context_view = new DBView("SELECT role, content, date_time FROM [Context] WHERE user=? AND date_time >= ? ORDER BY date_time", [$this->user_id, $user_data->context_start]);
        
        $context = [
            ['role' => 'system', 'content' => $prompt->prompt]
        ];
        while ($context_view->next()) {
            $context[] = ['role' => $context_view->role, 'content' => $context_view->content];
        }
        
        return $context;
    }
    
}
