<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

if (empty($managers)) {
    echo serialize(null);
} else {
    echo serialize(new InlineKeyboardMarkup([
        [['text' => '✅ Подтвердить', 'callback_data' => 'urgent'], ['text' => '⛔️ Отменить', 'callback_data' => 'cancel_urgent']]
    ]));
}
