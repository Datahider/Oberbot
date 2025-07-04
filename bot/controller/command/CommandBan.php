<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\banned;
use losthost\telle\Bot;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionById;
use function \losthost\Oberbot\sendMessage;

class CommandBan extends AbstractAuthCommand {
    
    const COMMAND = 'ban';

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        if ($this->isPrivateSupportGroup()) {
            // сообщить что вы не можете испольовать эту команду (посмотреть как в остальных)
        } else {
            $userid_2_ban = $this->getUserIdToBan();
            
            $banned = new banned(['user_id' => $userid_2_ban], true);
            if ($banned->isNew()) {
                $banned->write();
                sendMessage(__("Пользователь заблокирован"), null, null, $message->getMessageThreadId());
            }
            
            // Сообщить, что пользователь заблокирован (только в ветку, пользователю сообщать не надо)
        }
        
        
        return true;
        
    }
    
    protected function isPrivateSupportGroup() {
        if (Bot::$chat->id == Bot::param('chat_for_private_tickets', null)) {
            return true;
        }
        return false;
    }

    protected static function permit(): int {
        return static::PERMIT_AGENT;
    }

    public static function description(): array {
        return [
            'default' => 'Заблокировать пользователя приватной поддержки (Пользователя, который пишет в чат бота)'
        ];
    }
}
