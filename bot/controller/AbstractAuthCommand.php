<?php

namespace losthost\Oberbot\controller;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use function \losthost\Oberbot\isAgent;

abstract class AbstractAuthCommand extends AbstractHandlerCommand {
    
    const PERMIT_NONE = -1;
    const PERMIT_AGENT = 0;
    const PERMIT_USER = 1;
    const PERMIT_BOTH = 2;
    
    const PERMIT = self::PERMIT_NONE;

    public function handleUpdate(\TelegramBot\Api\BaseType &$data): bool {
        
        $from_id = $data->getFrom()->getId();
        $chat_id = $data->getChat()->getId();
        
        if (false
                || isAgent($from_id, $chat_id) && static::PERMIT == static::PERMIT_BOTH  
                || isAgent($from_id, $chat_id) && static::PERMIT == static::PERMIT_AGENT  
                || !isAgent($from_id, $chat_id) && static::PERMIT == static::PERMIT_BOTH  
                || !isAgent($from_id, $chat_id) && static::PERMIT == static::PERMIT_USER)  
                {
            return parent::handleUpdate($data);
        } 
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        if (isAgent($from_id, $chat_id)) {
            $view->show('auth_users_only', null, ['handler' => $this]);
        }
        $view->show('auth_agents_only', null, ['handler' => $this]);
        
        return true;
    }
}
