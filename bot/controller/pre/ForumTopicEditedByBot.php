<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;

class ForumTopicEditedByBot extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getForumTopicEdited() && $message->getFrom()->getId() == Bot::param('bot_userid', null)) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        Bot::$api->deleteMessage(Bot::$chat->id, $message->getMessageId());
        return true;
    }
}
