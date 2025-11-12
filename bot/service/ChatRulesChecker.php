<?php

namespace losthost\Oberbot\service;

use TelegramBot\Api\Types\Message;
use losthost\Oberbot\data\chat_settings;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ChatRulesChecker {

    protected Message $message;
    protected array $checkers;
    protected ticket $ticket;
    protected ?string $subject;
    protected int $chat_id;
    protected int $message_id;
    protected int $thread_id;


    public function __construct(Message &$message) {
        
        $this->message = $message;
        $this->chat_id = $message->getChat()->getId();
        $this->message_id = $message->getMessageId();
        $this->thread_id = $message->getMessageThreadId();
        
        $this->ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
        
        $this->subject = ($this->thread_id == $this->message_id-1) ? $this->ticket->title : null;
        
        $chat_id = $message->getChat()->getId();
        $chat = new chat(['id' => $chat_id]);
        $settings = new chat_settings(['id' => $chat->chat_settings_id]);
        
        $this->checkers = explode(' ', $settings->rules_checkers);
    }
    
    public static function forMessage(Message &$message) : static {
        $me = new static($message);
        return $me;
    }
            
    
    function check() {

        $text = $this->message->getText();
        if (!$text) {
            return;
        }

        foreach ($this->checkers as $checker_class) {
            
            $checker = new $checker_class($this->ticket->id, $this->subject);
            
            if (!$checker->isUseable()) {
                continue;
            }
            
            $check_result = $checker->check($text);

            if ($check_result !== true) {
                $this->brokenRulesMessage($check_result['text'], $check_result['buttons']);
            }
        }
    }

    function brokenRulesMessage(string $text, ?array $buttons) {
    
        
        $keyboard = $buttons ? new InlineKeyboardMarkup($buttons) : null;
        Bot::$api->sendMessage($this->chat_id, $text, null, false, $this->message_id, $keyboard, false, $this->thread_id);
        
    }
    
}
