<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class note_mention extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'note_uuid' => 'CHAR(36) NOT NULL',
        'user_id' => 'BIGINT(20) NOT NULL',
        'PRIMARY KEY' => 'id',
    ];
    
    
}
