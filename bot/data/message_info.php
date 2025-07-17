<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;
/**
 * Description of chat_message_thread
 *
 * @author drweb
 */
class message_info extends DBObject {
    
    const METADATA = [
        'chat_id' => 'BIGINT(20) NOT NULL',
        'message_id' => 'BIGINT(20) NOT NULL',
        'thread_id' => 'BIGINT(20)',
        'user_id' => 'BIGINT(20) NOT NULL',
        'PRIMARY KEY' => ['chat_id', 'message_id'],
    ];
}
