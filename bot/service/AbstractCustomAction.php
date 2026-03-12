<?php

namespace losthost\Oberbot\service;

abstract class AbstractCustomAction {
    
    abstract public function do(int $message_id, int $chat_id, ?int $message_thread_id, int $user_id, ?string $param);
    
}
