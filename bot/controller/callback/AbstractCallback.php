<?php

namespace losthost\Oberbot\controller\callback;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\Oberbot\service\Service;
use losthost\telle\Bot;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\isChatAdministrator;
use function \losthost\Oberbot\isAgent;

abstract class AbstractCallback extends AbstractHandlerCallback {
    
    const CALLBACK_DATA_PATTERN = false;
    
    const PERMIT_NONE = 0;
    const PERMIT_PRIVATE = 1;
    const PERMIT_ADMIN = 2;
    const PERMIT_AGENT = 4;
    const PERMIT_USER = 8;
    
    const PERMIT = self::PERMIT_NONE;
    
    protected array $matches=[];

    abstract function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query) : string|bool;
    
    public function __construct() {
        parent::__construct();
        if (!static::CALLBACK_DATA_PATTERN) {
            throw new \Exception('You should initialize const CALLBACK_PATTERN with PCRE compatible patttern.');
        }
    }

    protected function checkRights(\TelegramBot\Api\Types\CallbackQuery &$callback_query) : true|string {
        
        $from_id = $callback_query->getFrom()->getId();
        $chat_id = $callback_query->getMessage()->getChat()->getId();
        
        if ($from_id == $chat_id) {
            if (static::PERMIT & static::PERMIT_PRIVATE) { return true; }
        }
        
        if (isChatAdministrator($from_id, $chat_id) ) {
            if (static::PERMIT & static::PERMIT_ADMIN) { return true; }
        }
        
        if (isAgent($from_id, $chat_id)) {
            if (static::PERMIT & static::PERMIT_AGENT) { return true; }
        } 
            
        if (static::PERMIT & static::PERMIT_USER) { return true; } 
        
        return __('Не разрешено.');
    }
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        if (preg_match(static::CALLBACK_DATA_PATTERN, $callback_query->getData(), $this->matches)) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        
        $result = $this->checkRights($callback_query);
        if ($result === true) {
            $result = $this->processCallback($callback_query);
        }
        
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
