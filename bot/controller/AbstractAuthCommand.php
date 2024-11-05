<?php

namespace losthost\Oberbot\controller;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\BotView\BotView;
use function \losthost\Oberbot\isAgent;

abstract class AbstractAuthCommand extends AbstractHandlerCommand {
    
    const PERMIT_NONE = -1;
    const PERMIT_AGENT = 0;
    const PERMIT_USER = 1;
    const PERMIT_BOTH = 2;
    
    const PERMIT = PERMIT_NONE;

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
        
        // TODO -- сделать сообщение об отсутствии прав.
    }
}
