<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class chat_settings extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'name' => 'VARCHAR(16)',
        'owner_id' => 'BIGINT(20) NOT NULL',            // Telegram user who've created this chat settings
        'rules_text' => 'TEXT',                         // Текст правил
        'rules_entities' => 'TEXT',                     // Сущности 
        'rules_leave_chat_btn' => 'TINYINT(1)',         // Показывать кнопку "Покинуть чат" после правил
        'reaction_processing_id' => 'BIGINT(20)',       // Идентификатор процессора реакций
        'pricelist_id' => 'BIGINT(20)',                 // Идентификатор прайс-листа
        'pomodoro_like_timer' => 'TINYINT(1)',          // Использование таймера в стиле Pomodoro 
                                                        //  (не сбрасывает время при активности в заявке 
                                                        //  до окончания 25 мин интервала)
        'remind_malfunction_minutes' => 'TINYINT(4)',   // Присылать напоминание о новых, переоткрытых и отвеченных 
                                                        //  неисправностях каждые столько минут (0 или NULL -- не присылать)
        'PRIMARY KEY' => 'id',
        'INDEX OWNER' => 'owner_id',
    ];
}
