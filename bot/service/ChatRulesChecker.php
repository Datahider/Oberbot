<?php

namespace losthost\Oberbot\service;

use TelegramBot\Api\Types\Message;
use losthost\Oberbot\data\chat_settings;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\Oberbot\service\AIAbstractModerator;
use losthost\SimpleAI\data\Context;
use losthost\DB\DBList;

class ChatRulesChecker {

    protected Message $message;
    protected array $checkers;
    protected ticket $ticket;
    protected ?string $subject;
    protected int $chat_id;
    protected int $message_id;
    protected ?int $thread_id;


    public function __construct(Message &$message) {
        
        $this->message = $message;
        $this->chat_id = $message->getChat()->getId();
        $this->message_id = $message->getMessageId();
        $this->thread_id = $message->getMessageThreadId();
        
        try {
            $this->ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
            $this->subject = ($this->thread_id == $this->message_id-1) ? $this->ticket->title : null;
        } catch (Exception $ex) {
            Bot::logComment("Ticket not found for chat id: $this->chat_id; and thread id: $this->thread_id.");
            $this->subject = null;
        }
        
        
        $chat = new chat(['id' => $this->chat_id]);
        $settings = new chat_settings(['id' => $chat->chat_settings_id], true);
        if ($settings->isNew()) {
            Bot::logComment("No chat settings for chat id: $this->chat_id.");
            $this->checkers = [];
        } else {
            $this->checkers = explode(' ', $settings->rules_checkers);
        }
    }
    
    public static function forMessage(Message &$message) : static {
        $me = new static($message);
        return $me;
    }
            
    
    function check() {

        $text = $this->message->getText() ?? $this->message->getCaption() ?? "Пользователь отправил медиа-файл с описанием проблемы";
        if (!$text) {
            return;
        }

        foreach ($this->checkers as $checker_class) {

            if (!is_a($checker_class, AIAbstractModerator::class, true)) {
                Bot::logComment("<$checker_class> is not an AIAbstractModerator", __FILE__, __LINE__);
                continue;
            }
            
            if (!$this->subject && !$this->contextExists()) {
                continue;
            }
            
            $checker = new $checker_class($this->ticket->id, $this->subject);

            $check_result = $checker->check($text);

            if ($check_result !== true) {
                $this->brokenRulesMessage($check_result['text'], $check_result['buttons']);
            }
        }
    }

    protected function brokenRulesMessage(string $text, ?array $buttons) {
    
        
        $keyboard = $buttons ? new InlineKeyboardMarkup($buttons) : null;
        Bot::$api->sendMessage($this->chat_id, $text, null, false, $this->message_id, $keyboard, false, $this->thread_id);
        
    }
    
    protected function contextExists() {
        $system = new DBList(Context::class, ['user_id' => $this->ticket->id, 'role' => 'system']);
        return $system->next();
    }
    
}
