<?php

namespace losthost\Oberbot\handlers;

use losthost\Oberbot\handlers\AbstractCallback;
use losthost\Oberbot\data\topic_user;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\data\topic;

use function \losthost\Oberbot\message;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionById;

class CallbackLink extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^cmd_link$/";
            
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
    
        $topic = new topic(['topic_id' => $callback_query->getMessage()->getMessageThreadId(), 'chat_id' => $callback_query->getMessage()->getChat()->getId()]);
        
        $admin = new topic_admin(['user_id' => $callback_query->getFrom()->getId(), 'topic_number' => $topic->id], true);
        if (!$admin->isNew()) {
            $admin->delete();
        }
        
        $user = new topic_user(['user_id' => $callback_query->getFrom()->getId(), 'topic_number' => $topic->id], true);
        if ($user->isNew()) {
            $user->write();
            message('info', sprintf(__("Пользователь %s присоединился к заявке"), mentionById($callback_query->getFrom()->getId())), null, $callback_query->getMessage()->getMessageThreadId());
        }
        
        return true;
    }
}
