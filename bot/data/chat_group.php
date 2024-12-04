<?php
namespace losthost\Oberbot\data;

use losthost\DB\DB;
use losthost\DB\DBObject;
use losthost\DB\DBView;

class chat_group extends DBObject {

    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'chat_id' => 'BIGINT(20) NOT NULL',
        'chat_group' => 'VARCHAR(64) NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX chat_id_group' => ['chat_id', 'chat_group']
    ];
    
    public static function tableName() {
        return DB::$prefix. 'chat_groups';
    }
    
    static public function getUserLists(int $user_id) : array {
        
        $lists = new DBView(<<<FIN
            SELECT DISTINCT g.chat_group AS list
            FROM ober_chat_groups AS g
            LEFT JOIN ober_user_chat_role AS r ON r.chat_id = g.chat_id
            WHERE r.user_id = ? AND r.role = 'agent'                 
            FIN, [$user_id]);
        
        $result = [];
        
        while ($lists->next()) {
            $result[] = $lists->list;
        }
        
        return $result;
    }
}
