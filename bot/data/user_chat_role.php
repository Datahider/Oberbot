<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;
use losthost\DB\DB;

class user_chat_role extends DBObject {
    
    const ROLE_AGENT = 'agent';
    const ROLE_CUSTOMER = 'customer';
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'user_id' => 'BIGINT(20) NOT NULL',
        'chat_id' => 'BIGINT(20) NOT NULL',
        'role' => 'ENUM ("agent", "customer")',
        'updated' => 'DATETIME NOT NULL DEFAULT "2024-11-23 22:00:00"',
        'PRIMARY KEY' => 'id', 
        'UNIQUE INDEX USER_CHAT' => ['user_id', 'chat_id']
    ];
    
    public function __get($name): mixed {
        if ($name === 'role' && empty($this->__data['role'])) {
            return static::ROLE_CUSTOMER;
        }
        return parent::__get($name);
    }
    
    protected function beforeInsert($comment, $data) {
        $this->__data['updated'] = date_create()->format(DB::DATE_FORMAT);
        parent::beforeInsert($comment, $data);
    }
    
    protected function beforeUpdate($comment, $data) {
        $this->__data['updated'] = date_create()->format(DB::DATE_FORMAT);
        parent::beforeUpdate($comment, $data);
    }
    
    protected function beforeModify($name, $value) {
        if ($name == 'updated') {
            throw new \Exception('You cannot explicitly change the "updated" field');
        }
        parent::beforeModify($name, $value);
    }
}
