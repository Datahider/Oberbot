<?php

namespace losthost\Oberbot\view;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\Oberbot\controller\action\AbstractMenuAction;

use function \losthost\Oberbot\sendMessage;

class Menu {
    
    protected string $name;
    protected string $description;
    
    public function __construct(string $name, string $description, array $actions) {
        
        ;
    }
    
    public function show(int $message_id) : int {
        
    }
    
    protected function getText() : string {
        
    }
    
    protected function getButtons() : InlineKeyboardMarkup {
        
    }
}
