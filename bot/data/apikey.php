<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class apikey extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'secret' => 'VARCHAR(32) NOT NULL',
        'PRIMARY_KEY' => 'id',
    ];
}
