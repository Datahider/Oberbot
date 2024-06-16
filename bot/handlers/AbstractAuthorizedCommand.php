<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\telle\Bot;

use function \losthost\Oberbot\message;

abstract class AbstractAuthorizedCommand extends AbstractHandlerCommand {

    const ERROR_NOT_ALLOWED = false;
    
    abstract protected function processMessage(\TelegramBot\Api\Types\Message &$message) : bool;
    abstract protected function isAllowed() : bool;

    public function __construct() {
        parent::__construct();
        if (!static::ERROR_NOT_ALLOWED) {
            throw new \Exception('You should initialize const ERROR_NOT_ALLOWED with error text for not authorized users.');
        }
    }
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        if (!$this->isAllowed()) {
            message('error', static::ERROR_NOT_ALLOWED, 'Недостаточно прав');
            return true;
        }
    
        return $this->processMessage($message);
    }
    
}
