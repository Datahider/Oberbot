<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\action\ActionCreateTicket;
use losthost\telle\Bot;
use losthost\Oberbot\data\ticket;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;

class CommandSub extends AbstractAuthCommand {
    
    const COMMAND = 'sub';
    const DESCRIPTION = [
        'default' => 'Создание подзадачи',
        'all_group_chats' => 'Создание подзадачи'
    ];
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected array $m;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $this->check2($message);
        
        $title = $this->getTitle($message);
        $messages = $this->getMessages($message);
        
        $old_ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
        $ticket = ActionCreateTicket::do(Bot::$chat->id, Bot::$chat->id, $message->getFrom()->getId(), $title, $messages);
        
        $old_ticket->waitTask($ticket->id);

        return true;
    }
    
    protected function check2(\TelegramBot\Api\Types\Message &$message) {
        $this->m = [];
        preg_match_all("/^(.*)$/m", $this->args, $this->m);
        
        if ($message->getIsTopicMessage()) {
            if ($message->getReplyToMessage()->getMessageId() == $message->getMessageThreadId()) {
                $reply_to_message = null;
            } else {
                $reply_to_message = $message->getReplyToMessage()->getMessageId();
            }
        } else {
            $reply_to_message = $message->getReplyToMessage();
        }
        
        if (!$reply_to_message && !isset($this->m[1][1])) {
            throw new \Exception('Команда /sub должна цитировать сообщение или содержать более одной строки текста.');
        }
    }
    
    protected function getTitle(\TelegramBot\Api\Types\Message &$message) : string {

        if ($this->args) {
            $title = $this->args;
        } elseif ($message->getQuote()) {
            $title = $message->getQuote()->getText();
        } elseif ($message->getReplyToMessage()) {
            $title = $message->getReplyToMessage()->getText() 
                    ? $message->getReplyToMessage()->getText() 
                    : $message->getReplyToMessage()->getCaption();
            if (!$title) {
                $title = __('Новая заявка из сообщения');
            }
        }

        return $title;
    }
    
    protected function getMessages(\TelegramBot\Api\Types\Message &$message) : array {
        $result = [];
        
        if ($message->getReplyToMessage() && $message->getReplyToMessage()->getMessageId() != $message->getMessageThreadId()) {
            $result[] = $message->getReplyToMessage()->getMessageId();
        }
 
        $index = 1;
        $text = '';
        while (isset($this->m[1][$index])) {
            $text .= $this->m[1][$index]. "\n";
            $index++;
        }
        
        if ($text) {
            $result[] = $this->m[1][0]. "\n$text";
        }
        
        return $result;
    }
}
