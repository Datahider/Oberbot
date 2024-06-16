<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerCommand;

/**
 * Обработка команды /a в групповом чате для ответа в приватный чат
 *
 * @author drweb
 */
class CommandA extends AbstractHandlerCommand {
    
    const COMMAND = 'a';
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
    }
}
