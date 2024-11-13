<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class user_meta extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'user_id' => 'BIGINT(20) NOT NULL',
        'name' => 'VARCHAR(128) NOT NULL',
        'value' => 'TEXT',
        'PRIMARY KEY' => 'id',
    ];
    
    static public function get(int $user_id, string $name, ?string $default=null) : string {
        $meta = new user_meta(['user_id' => $user_id, 'name' => $name], true);
        if ($meta->isNew()) {
            $meta->value = $default;
            $meta->write();
        }
        return $meta->value;
    }
    
    static public function set(int $user_id, string $name, string $value) {
        $meta = new user_meta(['user_id' => $user_id, 'name' => $name], true);
        $meta->value = $value;
        $meta->write();
        return $value;
    }
}
