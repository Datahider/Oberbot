<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [['text' => '➕', 'callback_data' => 'link'], ['text' => '‼️', 'callback_data' => 'urgent']]
]));