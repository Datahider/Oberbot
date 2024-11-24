<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\telle\Bot;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;

class CommandHelp extends AbstractAuthCommand {
    
    const COMMAND = 'help';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER | self::PERMIT_PRIVATE;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        // нужно перенаправять в приват, где писать ссылку для возврата к текущему сообщению
        // а уже в привате давать помощь в зависимости от роли пользователя в этом чате
        // и если пользователь админ -- дополнительные инструкции для администратора
        
        if ($this->current_permit == self::PERMIT_PRIVATE) {
            $this->privateHelp($message);
        } else {
            $this->groupHelp($message);
        }
        
        return true;
    }
    
    protected function privateHelp(\TelegramBot\Api\Types\Message &$message) {
        
    }
    
    protected function groupHelp(\TelegramBot\Api\Types\Message &$message) {
        
        if ($message->getIsTopicMessage()) {
            $thread_id = $message->getMessageThreadId();
        } else {
            $thread_id = 1;
        }
        $chat_id = $message->getChat()->getId();
        $message_id = $message->getMessageId();
        $bot_username = Bot::param('bot_username', 'oberbot');
        $text = __('Сообщение помощи в группе');
        $kbd  = [
            [[ 'text' => __('Связаться с поддержкой'), 'callback_data' => 'call_help']],
            [[ 'text' => __('Посмотреть справку'), 'url' => "t.me/$bot_username?start=help_{$chat_id}_{$thread_id}_{$message_id}"]],
        ];
        sendMessage($text, $kbd, $chat_id, $thread_id);
        
    }
    
    static public function getSupportKeyboardArray() : array {
        
        return [
            [[ 'text' => __('Связаться с поддержкой'), 'callback_data' => 'call_help']],
        ];
    }
}
