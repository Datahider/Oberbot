<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\Oberbot\view\Emoji;

echo serialize(new InlineKeyboardMarkup([
    [[ 'text' => Emoji::ACTION_PLAY. " Начать работу", 'callback_data' => 'continue'] ],
]));