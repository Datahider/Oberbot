<?php
namespace losthost\Oberbot\data;

use losthost\DB\DB;
use losthost\DB\DBObject;

class note extends DBObject {

    const METADATA = [
        'uuid' => 'CHAR(36) NOT NULL',
        'note' => 'VARCHAR(4096) NOT NULL',
        'chat_id' => 'BIGINT(20) NOT NULL',
        'user_id' => 'BIGINT(20) NOT NULL',
        'topic_id' => 'BIGINT(20) NOT NULL',
        'time' => 'BIGINT(20) NOT NULL',
        'PRIMARY KEY' => 'uuid',
    ];
    
    public static function tableName() {
        return DB::$prefix. 'notes';
    }
}
