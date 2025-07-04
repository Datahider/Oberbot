<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class chat_apikey extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'chat_id' => 'BIGINT(20) NOT NULL',
        'apikey_id' => 'BIGINT(20) NOT NULL',
        'permissions' => 'SET("report")',
        'PRIMARY_KEY' => 'id',
    ];
}
