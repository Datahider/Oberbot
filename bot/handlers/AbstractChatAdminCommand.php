<?php

namespace losthost\Oberbot\handlers;

use losthost\Oberbot\handlers\AbstractAuthorizedCommand;
use losthost\telle\Bot;

use function \losthost\Oberbot\isChatAdministrator;

abstract class AbstractChatAdminCommand extends AbstractAuthorizedCommand {
    
    const ERROR_NOT_ALLOWED = 'Только администраторы группы имеют право на эту операцию.';
    
    abstract protected function processMessage(\TelegramBot\Api\Types\Message &$message) : bool;

    protected function isAllowed() : bool {
        if (isChatAdministrator(Bot::$user->id, Bot::$chat->id)) {
            return true;
        }
        return false;
    }
    
    
    
}
