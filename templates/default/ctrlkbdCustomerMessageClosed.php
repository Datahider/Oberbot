<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [[ 'text' => '❌ Переоткрыть', 'callback_data' => 'reopen']],
]));