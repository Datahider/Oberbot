<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\Oberbot\view\Emoji;

echo serialize(new InlineKeyboardMarkup([
    [[ 'text' => '👌 Понятно', 'callback_data' => 'TicketCreatingTip']],
]));
