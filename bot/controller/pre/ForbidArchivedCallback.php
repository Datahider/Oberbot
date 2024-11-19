<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;

use function \losthost\Oberbot\isAgent;

class ForbidArchivedCallback extends AbstractHandlerCallback {
    
    protected $chat_id;
    protected $user_id;
    protected $message_id;
    
    protected function check(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        $this->chat_id = $callback_query->getMessage()->getChat()->getId();
        $this->user_id = $callback_query->getFrom()->getId();
        $this->message_id = $callback_query->getMessage()->getMessageId();
        
        try {

            $ticket = ticket::getByGroupThread($this->chat_id, $callback_query->getMessage()->getMessageThreadId());
            return $ticket->status == ticket::STATUS_ARCHIVED 
                    && $this->user_id != Bot::param('bot_userid', null);
            
        } catch (\Exception $ex) {
            return false;
        }
    }

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        Bot::$api->answerCallbackQuery($callback_query->getId());
        try {
            Bot::$api->editMessageReplyMarkup($this->chat_id, $this->message_id);
        } catch (\Exception $ex) {
            Bot::logException($ex);
        }
        return true;
    }
}
