<?php

namespace losthost\Oberbot\controller\command;

class CommandUnknown extends CommandNote {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if (preg_match("/^\/(?!\d+(\s|$))/", $message->getText())) {  // Обрабатывает любую строку, начинающуюся с / и не обработанную до этого 
                                                         // если только это не команда состоящая только из цифр. Т.к. тогда она будет обработана позже       
            return true;                                 // Чтобы бот не реагировал на непонятные команды в группах, относящиеся к другим ботам
        }
        return false;
    }
}
