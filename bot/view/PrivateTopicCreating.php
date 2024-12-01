<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class PrivateTopicCreating extends DBTracker {
    
    public function track(\losthost\DB\DBEvent $event) {
        $private_topic = $event->object;
        
        sendMessage(__('Ожидайте ответа оператора.'), null, $private_topic->user_id);
    }
}
