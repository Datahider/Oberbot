<?php

namespace losthost\Oberbot\controller\message;

abstract class AbstractPrivateMessage extends AbstractMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $parent_result = parent::check($message);
        
        if ($parent_result) {
            return $this->isPrivateMessage();
        }
        return false;
    }
}
