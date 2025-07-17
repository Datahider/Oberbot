<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class chat_settings extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'name' => 'VARCHAR(16)',
        'owner_id' => 'BIGINT(20) NOT NULL', // Telegram user who've created this chat settings
        'rules_text' => 'TEXT',
        'rules_entities' => 'TEXT',
        'reaction_processing_id' => 'BIGINT(20)',
        'pricelist_id' => 'BIGINT(20)',
        'PRIMARY KEY' => 'id',
        'INDEX OWNER' => 'owner_id',
    ];
}
