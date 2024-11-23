<?php

namespace losthost\Oberbot\controller\command;

class CommandNew extends CommandOff {

    const COMMAND = 'new';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function banUser() {
        // do nothing //;
    }
    
    protected function prepareData(\TelegramBot\Api\Types\Message &$message) {

        ($this->new_title = $this->args) 
                || ($this->new_title = $message->getQuote() ? $message->getQuote()->getText() : null) 
                || ($this->new_title = $this->reply_message->getText()) 
                || ($this->new_title = $this->reply_message->getCaption())
                || ($this->new_title = 'Новая заявка из сообщения');
    }
}
