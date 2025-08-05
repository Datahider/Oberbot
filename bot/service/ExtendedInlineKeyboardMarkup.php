<?php

namespace losthost\Oberbot\service;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use function \losthost\Oberbot\__;

class ExtendedInlineKeyboardMarkup extends InlineKeyboardMarkup {
    
    static public function fromLinearArray(array $buttons) : static {
    
        $buttons[] = null;
        $keyboard = [];
        $line = [];
        
        foreach ($buttons as $text) {
            if ($text === null) {
                static::addLine($keyboard, $line);
                continue;
            }
            
            $button = ['text' => __($text), 'callback_data' => $text];
            $line[] = $button;
        }
        
        return new static($keyboard);
    }
    
    static protected function addLine(array &$keyboard, array &$line) {
        if (!empty($line)) {
            $keyboard[] = $line;
            $line = [];
        }
    }
}
