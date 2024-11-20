<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class chat_user extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'chat_id' => 'BIGINT(20)',
        'user_id' => 'BIGINT(20)',
        'last_seen' => 'DATETIME NOT NULL',
        'PRIMARY KEY' => 'id',
        'INDEX index_last_seen' => 'last_seen'
    ];
    
    static public function update_last_seen(int $chat_id, int $user_id, string|\DateTime|\DateTimeImmutable $time_seen = 'now') {
        
        if (is_string($time_seen)) {
            $time_seen = new \DateTime($time_seen);
        }
        
        $entry = new static(['chat_id' => $chat_id, 'user_id' => $user_id], true);
        $entry->last_seen = $time_seen;
        $entry->write();
    }
}
