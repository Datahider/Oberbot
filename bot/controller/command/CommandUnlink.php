<?php

namespace losthost\Oberbot\controller\command;

class CommandUnlink extends AbstractAuthCommand {
    
    const COMMAND = 'unlink';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        return true;
    }
}
