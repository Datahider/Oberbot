<?php

namespace losthost\Oberbot\service;

use losthost\telle\Bot;
use losthost\Oberbot\data\ticket;

class PenaltyForLying extends AbstractCustomAction {
    
    #[\Override]
    public function do(int $message_id, int $chat_id, ?int $message_thread_id, int $user_id, ?string $minutes) {
        
        // Блокируем пользователя на сутки
        $until = time() + 60 * 1440;
        try {
            Bot::$api->restrictChatMember($chat_id, $user_id, $until);
        } catch (\Throwable $e) {
            Bot::logThrowable($e);
        }
        
        // Закрываем тикет
        // Отправляем тикет в архив
        try {
            $ticket = ticket::getByGroupThread($chat_id, $message_thread_id);
            $ticket->close();
            $ticket->archive();
        } catch (\Throwable $e) {
            Bot::logThrowable($e);
        }
        
    }
}