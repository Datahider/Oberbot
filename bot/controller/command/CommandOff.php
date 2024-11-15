<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\telle\Bot;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\ticket;

class CommandOff extends AbstractAuthCommand {

    const COMMAND = 'off';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $matches = [];
        preg_match("/^(\d*)\s*(.*)$/", $this->args, $matches);
        
        if (!is_numeric($matches[1])) {
            $matches[1] = 10;
        }
        
        $reply_message = $message->getReplyToMessage();
        
        $matches[2] 
                || $matches[2] = $message->getQuote() 
                || $matches[2] = $reply_message->getText() 
                || $matches[2] = $reply_message->getCaption()
                || $matches[2] = 'Новая заявка из сообщения';
        
        if ($matches[1] != 0 ) {
            $ban_time = 60 * $minutes;
            Bot::$api->restrictChatMember(Bot::$chat->id, Bot::$user->id, \time() + $ban_time);
        }
        
        $forum_topic = Bot::$api->createForumTopic(Bot::$chat->id, $matches[2], Service::getRandomTopicIconColor());
        $new_thread = $forum_topic->getMessageThreadId();
        Bot::$api->forwardMessage(Bot::$chat->id, Bot::$chat->id, $reply_message->getMessageId(), false, false, $new_thread);
        Service::message('none', Service::__('Исходное сообщение: '. Service::messageLink(Bot::$chat->id, $message->getMessageThreadId(), $message->getMessageId())), null, $new_thread);
        
        $user_id = $reply_message->getFrom()->getId();
        $new_ticket = ticket::create(Bot::$chat->id, $new_thread, $matches[2], $user_id);
        $new_ticket->linkCustomer($user_id);
        $new_ticket->accept();
        
    }
    
    
}
