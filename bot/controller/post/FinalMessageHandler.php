<?php

namespace losthost\Oberbot\controller\post;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\service\ChatRulesChecker;

class FinalMessageHandler extends AbstractHandlerMessage {
    
    const IS_FINAL = true;

    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $text = $message->getText() ?? $message->getCaption();
        if ($text && !preg_match("/^\//", $text)) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
               
        ChatRulesChecker::forMessage($message)->check();
        
        return true;
    }
}
