<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class price_lists extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'owner_id' => 'BIGINT(20) NOT NULL',
        'name' => 'VARCHAR(16) NOT NULL',
        'PRIMARY KEY' => 'id',
        'INDEX OWNER' => 'owner_id'
    ];
}
