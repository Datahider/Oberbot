<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\telle\Bot;
use losthost\Oberbot\data\user_meta;

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
        
        $text = __('Сообщение помощи в группе');
        $kbd  = $this->getSupportKeyboardArray($this->chat_id);
        sendMessage($text, $kbd, $this->chat_id, $this->thread_id);
        
    }
    
    static public function getSupportKeyboardArray(int $chat_id) : array {
        
        return [
            [[ 'text' => __('Пригласить специалиста'), 'callback_data' => 'call_help']],
            [[ 'text' => __('Написать в поддержку'), 'callback_data' => 'go_help']],
        ];
    }
}
