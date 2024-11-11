<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [[ 'text' => '❌ Не выполнено/Переоткрыть', 'callback_data' => 'reopen']],
    [[ 'text' => '🙁', 'callback_data' => 'bad'], [ 'text' => '😐', 'callback_data' => 'acceptable'], [ 'text' => '😊', 'callback_data' => 'good']]
]));