<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class api_ticket_request extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'apikey_id' => 'BIGINT(20) NOT NULL',
        'chat_id' => 'BIGINT(20) NOT NULL',
        'user_id' => 'BIGINT(20) NOT NULL',
        'title' => 'VARCHAR(128) NOT NULL',
        'text' => 'TEXT NOT NULL',
        'created' => 'DATETIME NOT NULL',
        'ticket_id' => 'BIGINT(20)',
        'error' => 'VARCHAR(500)',
        'PRIMARY KEY' => 'id',
    ];
}
