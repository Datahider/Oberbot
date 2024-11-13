<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\telle\Bot;

$bot_username = Bot::param('bot_username', null);

echo serialize(new InlineKeyboardMarkup([
    [[ 'text' => '🔍 Быстрый просмотр', 'callback_data' => "hid_$note->uuid" ], [ 'text' => '©️ Открыть в боте', 'url' => "t.me/$bot_username?start=$note->uuid"], ],
]));