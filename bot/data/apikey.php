<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class apikey extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'secret' => 'VARCHAR(64) NOT NULL',
        'user_id' => 'BIGINT(20) NOT NULL',
        'comment' => 'VARCHAR(255) NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX secret' => 'secret',
    ];
}
