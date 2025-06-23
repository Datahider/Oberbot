<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class banned extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL',
        'user_id' => 'BIGINT(20) NOT NULL',
        'till' => 'DATETIME',
        'PRIMARY KEY' => 'id',
        'INDEX TILL' => 'till'
    ];
    
}
