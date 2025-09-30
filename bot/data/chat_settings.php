<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;
use losthost\DB\DBValue;

class chat_settings extends DBObject {
    
    const DEFAULT_REMIND_RUNNING_TIMER_MINUTES = 25;
    const DEFAULT_STOP_RUNNING_TIMER_MINUTES = 30;
    
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
        'remind_running_timer_minutes' => 'TINYINT(4)', // Напоминать о работающем таймере через (минут)
        'stop_running_timer_minutes' => 'TINYINT(4)',   // Останавливать работающий таймер через (минут)
        'remind_malfunction_minutes' => 'TINYINT(4)',   // Присылать напоминание о новых, переоткрытых и отвеченных 
                                                        //  неисправностях каждые столько минут (0 или NULL -- не присылать)
        'PRIMARY KEY' => 'id',
        'INDEX OWNER' => 'owner_id',
    ];
    
    static public function getChatSettinsByChatId(int $chat_id) {
        
        $settings_id = new DBValue("SELECT chat_settings_id AS value FROM [chat] WHERE id = ?", $chat_id);
        $settings = new static(['id' => $settings_id->value], true);
        return $settings;
        
    }
    
    public function __get($name) {
        $value = parent::__get($name);
        
        if ($value === null) {
            switch ($name) {
                case 'remind_running_timer_minutes':
                    return self::DEFAULT_REMIND_RUNNING_TIMER_MINUTES;
                case 'stop_running_timer_minutes';
                    return self::DEFAULT_STOP_RUNNING_TIMER_MINUTES;
            }
        }
        
        return $value;
    }
}
