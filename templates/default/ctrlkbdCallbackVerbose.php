<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [['text' => 'Руководитель', 'callback_data' => 'cheef']],
    [['text' => 'Технический специалист', 'callback_data' => 'techno']],
    [['text' => 'Сервисная компания', 'callback_data' => 'service_company']],
]));