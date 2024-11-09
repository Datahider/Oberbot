<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [['text' => '➕', 'callback_data' => 'cmd_link'], ['text' => '‼️', 'callback_data' => 'cmd_urgent']]
]));