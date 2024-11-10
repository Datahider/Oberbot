<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [['text' => 'Подробнее', 'callback_data' => 'verbose']]
]));
