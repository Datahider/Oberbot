<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\Oberbot\data\user_chat_role;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\textMentionByIdArray;

class AgentLeftTheGroup extends AbstractHandlerMessage {
    
    protected int $left_user_id;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $left_chat_member = $message->getLeftChatMember();
        if ($left_chat_member) {
            $this->left_user_id = $left_chat_member->getId();
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $user_chat_role = new user_chat_role(['user_id' => $this->left_user_id, 'chat_id' => Bot::$chat->id], true);
        if (!$user_chat_role->isNew()) {
            $user_chat_role->delete();
            Bot::logComment(__('%mention% was removed from agent list for chat %chat%', ['mention' => textMentionByIdArray([$this->left_user_id]), 'chat' => Bot::$chat->title]));
        }
        return true;
    }
}
