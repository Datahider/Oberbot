<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\telle\Bot;

class DeleteMessage extends AbstractBackgroundProcess {
    
    public function run() {
        $chat_msg = explode(' ', $this->param);
        try {
            Bot::$api->deleteMessage($chat_msg[0], $chat_msg[1]);
        } catch (\Exception $ex) {
            Bot::logException($ex);    
        }
    }
}
