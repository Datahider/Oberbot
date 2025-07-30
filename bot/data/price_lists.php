<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class price_lists extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'owner_id' => 'BIGINT(20) NOT NULL',
        'name' => 'VARCHAR(16) NOT NULL',
        'type1_item' => 'DECIMAL(15, 2)',
        'type1_hour' => 'DECIMAL(15, 2)',
        'type2_item' => 'DECIMAL(15, 2)',
        'type2_hour' => 'DECIMAL(15, 2)',
        'type3_item' => 'DECIMAL(15, 2)',
        'type3_hour' => 'DECIMAL(15, 2)',
        'type4_item' => 'DECIMAL(15, 2)',
        'type4_hour' => 'DECIMAL(15, 2)',
        'type5_item' => 'DECIMAL(15, 2)',
        'type5_hour' => 'DECIMAL(15, 2)',
        'type6_item' => 'DECIMAL(15, 2)',
        'type6_hour' => 'DECIMAL(15, 2)',
        'PRIMARY KEY' => 'id',
        'INDEX OWNER' => 'owner_id'
    ];
}
