<?php
namespace losthost\Oberbot\data;

use losthost\DB\DB;
use losthost\DB\DBObject;

class topic_user extends DBObject {

    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'topic_number' => 'BIGINT(20) NOT NULL',
        'user_id' => 'BIGINT(20) NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX topic_user_id' => ['topic_number', 'user_id']
    ];
    
    public static function tableName() {
        return DB::$prefix. 'topic_users';
    }
}
