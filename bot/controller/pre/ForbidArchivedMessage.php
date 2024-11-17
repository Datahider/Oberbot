<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;

use function \losthost\Oberbot\isAgent;

class ForbidArchivedMessage extends AbstractHandlerMessage {
    
    protected $chat_id;
    protected $user_id;
    protected $message_id;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $this->chat_id = $message->getChat()->getId();
        $this->user_id = $message->getFrom()->getId();
        $this->message_id = $message->getMessageId();
        
        try {

            $ticket = ticket::getByGroupThread($this->chat_id, $message->getMessageThreadId());
            return $ticket->status == ticket::STATUS_ARCHIVED 
                    && $this->user_id != Bot::param('bot_userid', null);
            
        } catch (\Exception $ex) {
            return false;
        }
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        try {
            Bot::$api->deleteMessage($this->chat_id, $this->message_id);
            Bot::$api->restrictChatMember($this->chat_id, $this->user_id, \time()+Bot::param('ban_for_archived', 300));        
        } catch (\Exception $ex) {
            Bot::logException($ex);
        }
        return true;
    }
}
