<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;
 
class session extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'user_id' => 'BIGINT(20)',
        'chat_id' => 'BIGINT(20)',
        'working_group' => 'VARCHAR(32)',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX USER_CHAT' => ['user_id', 'chat_id']
    ];
    
}
