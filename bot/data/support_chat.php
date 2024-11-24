<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;
use losthost\DB\DBList;

class support_chat extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20)',
        'invite_link' => 'VARCHAR(64) NOT NULL',
        'chat_id' => 'BIGINT(20)',
        'reserved_message_id' => 'BIGINT(20)',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX CHAT_ID' => 'chat_id'
    ];
    
    static public function getChatInviteLink(int $chat_id) {
        
        $support_chat = new static(['chat_id' => $chat_id], true);
        if ($support_chat->isNew()) {
            $list = new DBList(static::class, 'chat_id IS NULL LIMIT 1', []);
            if (!$support_chat = $list->next()) {
                throw new \Exception('Not support chats reserved');
            }
            $support_chat->chat_id = $chat_id;
            $support_chat->write();
        }
        return $support_chat->invite_link;
    }
}
