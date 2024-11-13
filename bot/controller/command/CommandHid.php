<?php

namespace losthost\Oberbot\controller\command;

use losthost\telle\Bot;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\note;
use losthost\telle\abst\AbstractHandlerMessage;

class CommandHid extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        
        if ($message->getChat()->getId() == $message->getFrom()->getId()) {
            return false;
        }
        
        $text = substr($message->getText(), 0, 5);
        
        if ($text == '/hid' || $text == '/hid ') {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $args = substr($message->getText(), 5);
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        if ($args) {
            Bot::$api->deleteMessage($group_id, $message->getMessageId());
            $mentioned_ids = Service::getMentionedIds($message);
            note::create($args, $group_id, $thread_id, $user_id, $mentioned_ids);
            
        } else {
            Service::message('info', 'Описание команды /hid', null, $thread_id);
        }
        
        return true;
    }

}
