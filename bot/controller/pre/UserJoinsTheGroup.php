<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerChatMember;
use losthost\telle\model\DBUser;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;
use function losthost\Oberbot\mentionById;

class UserJoinsTheGroup extends AbstractHandlerChatMember {
    
    protected int $user_id;
    
    protected function check(\TelegramBot\Api\Types\ChatMemberUpdated &$chat_member): bool {
        
        $old_chat_member = $chat_member->getOldChatMember();
        $old_status = $old_chat_member->getStatus();

        if ($old_status == 'left') {
            $user = $chat_member->getNewChatMember()->getUser();
            new DBUser($user);
            
            $this->user_id = $user->getId();
            return true;
        }
        
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\ChatMemberUpdated &$chat_member): bool {

        sendMessage(__('Привет %mention%! Пожалуйста ознакомьтесь с инструкцией по созданию задач', ['mention' => mentionById($this->user_id, true)]));
        return true;
    }
}
