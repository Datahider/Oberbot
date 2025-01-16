<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerChatMember;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;
use function losthost\Oberbot\mentionById;

class UserJoinsTheGroup extends AbstractHandlerChatMember {
    
    protected int $user_id;
    
    protected function check(\TelegramBot\Api\Types\ChatMemberUpdated &$chat_member): bool {
        
        $old_chat_member = $chat_member->getOldChatMember();
        if ($old_chat_member) {
            $old_status = $old_chat_member->getStatus();
        } else {
            $old_status = false;
        }
        
        if (!$old_status) {
            $this->user_id = $chat_member->getNewChatMember()->getUser()->getId();
            return true;
        }
        
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\ChatMemberUpdated &$chat_member): bool {

        sendMessage(__('Привет %mention%! Пожалуйста ознакомьтесь с инструкцией по созданию задач', ['mention' => mentionById($this->user_id, true)]));
        return true;
    }
}
