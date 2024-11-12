<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\chat;
use losthost\telle\Bot;

/**
 * Этот обработчик должен быть первым обработчиком сообщений
 * Он проверяет должен ли бот обрабатывать сообщения (не команды) в этом чате
 * Если не должен - просто делает вид что сообщение обработано ничего не делая и,
 * таким образом не давая сработать другим обработчикам
 */
class NonCommandChatCheckerHandler extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if (substr($message->getText(), 0, 1) === '/') {
            return false;
        }
        
        if (Bot::$chat->id === Bot::$user->id) {
            return false;
        }
        
        $chat = new chat(['id' => Bot::$chat->id], true);
        return $chat->process_tickets;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        // Do nothing
        return true;
    }
}
