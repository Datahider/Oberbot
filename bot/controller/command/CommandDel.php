<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\telle\Bot;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class CommandDel extends AbstractAuthCommand {
    
    const COMMAND = 'del';
    const PERMIT = self::PERMIT_AGENT;
    const DESCRIPTION = [
        'default' => 'Удаление выбранного сообщения',
        'all_group_chats' => 'Удаление выбранного сообщения'
    ];
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $reply_message_id = $message->getReplyToMessage()->getMessageId();
        $reply_message_chat_id = $message->getReplyToMessage()->getChat()->getId();
        
        if ($reply_message_id != $this->thread_id && $reply_message_chat_id == $this->chat_id) {
            Bot::$api->deleteMessage($reply_message_chat_id, $reply_message_id);
        } else {
            sendMessage(__('Описание команды /del'), null, $this->chat_id, $this->thread_id);
        }
        
        return true;
    }
    
    static protected function permit(): int {
        return self::PERMIT;
    }

    static public function description(): array {
        return self::DESCRIPTION;
    }
    
}
