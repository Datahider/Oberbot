<?php

namespace losthost\Oberbot\controller\command;

class CommandRemind extends AbstractAuthCommand {
    
    const COMMAND = 'remind';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
     
        return true;
    }
}
