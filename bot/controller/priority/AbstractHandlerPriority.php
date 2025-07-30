<?php

namespace losthost\Oberbot\controller\priority;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;

abstract class AbstractHandlerPriority extends AbstractHandlerMessage {
 
    abstract public function getPrompt() : string;
    
    public function getInlineKeyboard() {
        $keyboard = new InlineKeyboardMarkup([[['text' => __('Отмена создания набора настроек'), 'callback_data' => 'cancel_settings_create']]]);
    }

    public function showPrompt(int $chat_id, int $message_thread_id=null) : int {
        $message_id = sendMessage(__($this->getPrompt()), $this->getInlineKeyboard(), $chat_id, $message_thread_id);
        $this->setPriority(['prompt_message_id' => $message_id]);
        return $message_id;
    }
}
