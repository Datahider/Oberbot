<?php

namespace losthost\Oberbot\handlers;

use losthost\Oberbot\handlers\AbstractAuthorizedCommand;
use losthost\telle\Bot;

use function \losthost\Oberbot\isAgent;

abstract class AbstractChatAgentCommand extends AbstractAuthorizedCommand {
    
    protected function isAllowed(): bool {
        return isAgent(Bot::$user->id, Bot::$chat->id);
    }

}
