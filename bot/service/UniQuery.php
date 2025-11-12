<?php

namespace losthost\Oberbot\service;

use losthost\ReflexA\Mind\UserQuery;

class UniQuery extends UserQuery {

    protected string $name;
    
    public function __construct(int $user_id, $agent_name=null) {
        parent::__construct($user_id);
        $this->name = $agent_name ?? 'uni';
    }
    protected function agentName(): string {
        return $this->name;
    }
}
