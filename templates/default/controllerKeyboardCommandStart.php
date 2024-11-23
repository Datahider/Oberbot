<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [['text' => 'Οκ, что дальше? ➡️', 'callback_data' => 'verbose']]
]));
