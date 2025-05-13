<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\topic;
use losthost\DB\DB;

class ForumTopicCreatedHandler extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getForumTopicCreated() && !$message->getFrom()->isBot()) { // не обрабатываем от ботов
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $topic = new topic([
            'chat_id' => $message->getChat()->getId(),
            'topic_id' => $message->getMessageId(),
            'topic_title' => $message->getForumTopicCreated()->getName(),
            'last_activity' => \time(),
            'last_admin_activity' => 0,
            'status' => topic::STATUS_PENDING,
            'is_urgent' => false,
            'is_task' => true,
            'type' => topic::TYPE_REGULAR_TASK,
            'wait_for' => null,
            'wait_till' => date_create('+5 min')->format(DB::DATE_FORMAT)
        ], true);
        $topic->write();
        
        return true;
    }
}
