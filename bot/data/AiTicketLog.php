<?php

declare(strict_types=1);

namespace losthost\Oberbot\data;

use losthost\DB\DB;
use losthost\DB\DBObject;

final class AiTicketLog extends DBObject
{
    public const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'ticket_id' => 'BIGINT(20) NOT NULL',
        'source_chat_id' => 'BIGINT(20) NOT NULL',
        'telegram_message_id' => 'BIGINT(20) NOT NULL',
        'created_at' => 'DATETIME NOT NULL',
        'role' => 'VARCHAR(16) NOT NULL',
        'text' => 'TEXT NOT NULL',
        'attachments' => 'LONGTEXT NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX source_message' => ['source_chat_id', 'telegram_message_id'],
        'INDEX ticket' => 'ticket_id',
    ];

    public static function tableName(): string
    {
        return DB::$prefix . 'ai_ticket_log';
    }
}
