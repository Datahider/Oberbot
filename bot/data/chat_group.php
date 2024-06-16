<?php
namespace losthost\Oberbot\data;

use losthost\DB\DB;
use losthost\DB\DBObject;

class chat_group extends DBObject {

    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'chat_id' => 'BIGINT(20) NOT NULL',
        'chat_group' => 'VARCHAR(32) NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX chat_id_group' => ['chat_id', 'chat_group']
    ];
    
    public static function tableName() {
        return DB::$prefix. 'chat_groups';
    }
}
