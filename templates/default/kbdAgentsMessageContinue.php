<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\Oberbot\view\Emoji;

echo serialize(new InlineKeyboardMarkup([
    [[ 'text' => Emoji::ACTION_PAUSE, 'callback_data' => 'pause'], [ 'text' => Emoji::ACTION_NOTIFY, 'callback_data' => 'notify'], [ 'text' => Emoji::ACTION_DONE, 'callback_data' => 'done'], ],
]));