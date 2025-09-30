<?php

namespace losthost\Oberbot\controller\command;

class CommandUnknown extends CommandNote {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if (preg_match("/^\//", $message->getText())) {  // Обрабатывает любую строку, начинающуюся с / и не обработанную до этого 
            return true;                                 // Чтобы бот не реагировал на непонятные команды в группах, относящиеся к другим ботам
        }
        return false;
    }
}
