<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\telle\Bot;

abstract class AbstractCallback extends AbstractHandlerCallback {
    
    const CALLBACK_DATA_PATTERN = false;
    
    protected array $matches=[];
    
    abstract function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query) : string|bool;
    
    public function __construct() {
        parent::__construct();
        if (!static::CALLBACK_DATA_PATTERN) {
            throw new \Exception('You should initialize const CALLBACK_PATTERN with PCRE compatible patttern.');
        }
    }

    protected function isAllowed() {
        return true;
    }
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        if (preg_match(static::CALLBACK_DATA_PATTERN, $callback_query->getData(), $this->matches)) {
            return $this->isAllowed();
        }
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        
        $result = $this->processCallback($callback_query);
        
        if (is_bool($result)) {
            try {
                Bot::$api->answerCallbackQuery($callback_query->getId());
            } catch (\Exception $exc) {
                Bot::logException($exc);
            }
            return $result;
        } else {
            try {
                Bot::$api->answerCallbackQuery($callback_query->getId(), $result, true);
            } catch (\Exception $exc) {
                Bot::logException($exc);
            }
            return true;
        }
            
    }
}
