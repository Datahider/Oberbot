<?php

namespace losthost\Oberbot\controller\command;

class CommandOff extends AbstractAuthCommand {
    
    const COMMAND = 'off';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        return true;
    }
}
