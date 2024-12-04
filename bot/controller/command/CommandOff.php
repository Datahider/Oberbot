<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\telle\Bot;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\ticket;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\ifnull;

class CommandOff extends AbstractAuthCommand {

    const COMMAND = 'off';
    const PERMIT = self::PERMIT_AGENT;
    
    protected $minutes;
    protected $new_title;
    protected $reply_message;


    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {

        $this->checkIsReply($message);
        $this->prepareData($message);
        $this->banUser();
        $this->createTicket();
        
        return true;
    }

    protected function checkIsReply(\TelegramBot\Api\Types\Message &$message) {

        $this->reply_message = $message->getReplyToMessage();
        
        if (!$this->reply_message || $this->reply_message->getMessageId() == $message->getMessageThreadId() && $message->getIsTopicMessage()) {
            throw new \Exception(__('Команда /%command% должна быть использована в ответе на сообщение', ['command' => static::COMMAND]));
        }
        
    }
    protected function prepareData(\TelegramBot\Api\Types\Message &$message) {

        $matches = [];
        preg_match("/^(\d*)\s*(.*)$/", $this->args, $matches);
        
        if (!is_numeric($matches[1])) {
            $this->minutes = 10;
        }

        if ($matches[2]) {
            $this->new_title = $matches[2];
        } elseif ($message->getQuote()) {
            $this->new_title = $message->getQuote()->getText();
        } elseif ($message->getReplyToMessage()) {
            $this->new_title = $message->getReplyToMessage()->getText() 
                    ? $message->getReplyToMessage()->getText() 
                    : $message->getReplyToMessage()->getCaption();
            if (!$this->new_title) {
                $this->new_title = __('Новая заявка из сообщения');
            }
        }
        
    }
    
    protected function banUser() {
        if ($this->minutes != 0 ) {
            $ban_time = 60 * $this->minutes;
            try {
                Bot::$api->restrictChatMember(Bot::$chat->id, Bot::$user->id, \time() + $ban_time);
            } catch (\Exception $ex) {
                Bot::logException($ex);
            }
        }
    }
    
    protected function createTicket() {

        $forum_topic = Bot::$api->createForumTopic(Bot::$chat->id, $this->new_title, Service::getRandomTopicIconColor());
        $new_thread = $forum_topic->getMessageThreadId();
        Bot::$api->forwardMessage(Bot::$chat->id, Bot::$chat->id, $this->reply_message->getMessageId(), false, false, $new_thread);
        sendMessage(__('Исходное сообщение: '. Service::messageLink(Bot::$chat->id, ifnull($this->reply_message->getMessageThreadId(), 1), $this->reply_message->getMessageId())), null, null, $new_thread);
        
        $user_id = $this->reply_message->getFrom()->getId();
        $new_ticket = ticket::create(Bot::$chat->id, $new_thread, $this->new_title, $user_id);
        $new_ticket->linkCustomer($user_id);
        $new_ticket->accept();
        
    }
}
