<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

/**
 * хранит связь пользователя и его текущей заявки из личного сообщения боту
 *
 * @author drweb
 */
class private_topic extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'user_id' => 'BIGINT(20) NOT NULL',
        'ticket_id' => 'BIGINT(20) NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX USER' => 'user_id',
        'UNIQUE INDEX TICKET' => 'ticket_id'
    ];
}
