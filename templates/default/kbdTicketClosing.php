<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\Oberbot\view\Emoji;

echo serialize(new InlineKeyboardMarkup([
    [[ 'text' => '❌ Не выполнено/Переоткрыть', 'callback_data' => 'reopen']],
    [[ 'text' => Emoji::RATING_BAD, 'callback_data' => 'bad'], [ 'text' => Emoji::RATING_ACCEPTABLE, 'callback_data' => 'acceptable'], [ 'text' => Emoji::RATING_GOOD, 'callback_data' => 'good']]
]));