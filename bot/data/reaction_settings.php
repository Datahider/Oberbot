<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class reaction_settings extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT NOT NULL AUTO_INCREMENT',
        'chat_settings_id' => 'BIGINT NOT NULL',
        'reaction' => 'VARCHAR(6) NOT NULL',
        'message' => 'VARCHAR(1024)',
        'action' => 'ENUM("ban", "kick", "custom")',
        'custom_action_class' => 'VARCHAR(200)',
        'action_param' => 'VARCHAR(100)',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX IDX_SETTINGS_REACTION' => ['chat_settings_id', 'reaction']
    ];
}
