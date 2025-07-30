<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\chat_settings;
use losthost\Oberbot\data\chat;
use losthost\telle\Bot;
use function \losthost\Oberbot\__;

class CommandRules extends AbstractAuthCommand {
    
    const COMMAND = 'rules';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        $chat = new chat(['id' => Bot::$chat->id]);
        $settings = new chat_settings(['id' => $chat->chat_settings_id], true);
        
        if ($settings->isNew()) {
            Bot::$api->sendMessage(Bot::$chat->id, __('Правила для этого чата не определены.'), null, false, null, null, false, $message->getMessageThreadId());
        } else {
            Bot::$api->call('sendMessage', [
                'chat_id' => Bot::$chat->id,
                'text' => $settings->rules_text,
                'entities' => $settings->rules_entities,
                'message_thread_id' => $message->getMessageThreadId(),
                'reply_markup' => $this->leaveChatKeyboard()
            ]);
        }
        
        return true;
    }

    protected function leaveChatKeyboard() {
        
        return json_encode([
            'inline_keyboard' => [
                [['text' => __('Покинуть чат'), 'callback_data' => 'leave_chat']],
            ],
        ]);
    }
    protected static function permit(): int {
        return self::PERMIT_ADMIN | self::PERMIT_AGENT | self::PERMIT_USER;
    }

    public static function description(): array {
        return [
            'default' => 'Просмотр правил',
        ];
    }
    
}
