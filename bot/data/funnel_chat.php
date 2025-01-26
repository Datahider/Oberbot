<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class funnel_chat extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL',
        'owner_id' => 'BIGINT(20) NOT NULL',
        'invite_link' => 'VARCHAR(128) NOT NULL',
        'customer_id' => 'BIGINT(20)',
        'PRIMARY KEY' => 'id',
        'INDEX OWNER' => 'owner_id',
    ];
}
