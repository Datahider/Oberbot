<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class user_chat_role extends DBObject {
    
    const ROLE_AGENT = 'agent';
    const ROLE_CUSTOMER = 'customer';
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'user_id' => 'BIGINT(20) NOT NULL',
        'chat_id' => 'BIGINT(20) NOT NULL',
        'role' => 'ENUM ("agent", "customer")',
        'PRIMARY KEY' => 'id', 
        'UNIQUE INDEX USER_CHAT' => ['user_id', 'chat_id']
    ];
    
    public function __get($name): mixed {
        if ($name === 'role' && empty($this->__data['role'])) {
            return static::ROLE_CUSTOMER;
        }
        return parent::__get($name);
    }
}
