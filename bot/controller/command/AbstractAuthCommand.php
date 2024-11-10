<?php

namespace losthost\Oberbot\controller\command;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use function \losthost\Oberbot\isAgent;
use function \losthost\Oberbot\message;
use function \losthost\Oberbot\__;

abstract class AbstractAuthCommand extends AbstractHandlerCommand {
    
    const PERMIT_NONE = 0;
    const PERMIT_PRIVATE = 1;
    const PERMIT_ADMIN = 2;
    const PERMIT_AGENT = 4;
    const PERMIT_USER = 8;
    
    const PERMIT = self::PERMIT_NONE;

    public function handleUpdate(\TelegramBot\Api\BaseType &$data): bool {
        
        $from_id = $data->getFrom()->getId();
        $chat_id = $data->getChat()->getId();
        
        if ($from_id == $chat_id) {
            if (static::PERMIT & static::PERMIT_PRIVATE) {
                return parent::handleUpdate($data);
            }
            message('warning', sprintf(__('Команда /%s не предназначена для использования в приватном чате.'), static::COMMAND));
        } elseif ( isAgent($from_id, $chat_id) ) {
            if (static::PERMIT & static::PERMIT_AGENT) {
                return parent::handleUpdate($data);
            }
            message('warning', sprintf(__('Команда /%s не предназначена для использования агентами техподдержки.'), static::COMMAND), null, $data->getMessageThreadId());
        } else {
            if (static::PERMIT & static::PERMIT_USER) {
                return parent::handleUpdate($data);
            }
            message('warning', sprintf(__('Команда /%s не предназначена для использования пользователями.'), static::COMMAND), null, $data->getMessageThreadId());
        } 
        
        return true;
    }
}
