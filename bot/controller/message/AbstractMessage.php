<?php

namespace losthost\Oberbot\controller\message;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;

abstract class AbstractMessage extends AbstractHandlerMessage {
    
    protected bool $is_private;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getText()
            || $message->getAnimation()
            || $message->getAudio()
            || $message->getContact()
            || $message->getDocument()
            || $message->getPhoto()
            || $message->getText()
            || $message->getVideo()
            || $message->getVoice()) {
            
            $this->is_private = (Bot::$chat->id == Bot::$user->id);
            
            return true;
        }
        return false;
    }

    protected function isPrivateMessage() {
        return $this->is_private;
    }
}
