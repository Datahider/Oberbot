<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [[ 'text' => '❌ Не выполнено/Переоткрыть', 'callback_data' => 'cmd_reopen']],
    [[ 'text' => '🙁', 'callback_data' => 'cmd_bad'], [ 'text' => '😐', 'callback_data' => 'cmd_acceptable'], [ 'text' => '😊', 'callback_data' => 'cmd_good']]
]));