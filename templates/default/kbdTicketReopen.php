<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\Oberbot\view\Emoji;

echo serialize(new InlineKeyboardMarkup([
    [[ 'text' => Emoji::ACTION_PLAY, 'callback_data' => 'continue'], [ 'text' => Emoji::ACTION_DONE, 'callback_data' => 'done'], ],
]));