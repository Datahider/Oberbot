<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class chat extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL',
        'process_tickets' => 'TINYINT(1) NOT NULL',
        'language_code' => 'VARCHAR(3)',
        'delete_commands' => 'TINYINT(1)',
        'PRIMARY KEY' => 'id'
    ];
}
