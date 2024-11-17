<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

echo serialize(new InlineKeyboardMarkup([
    [['text' => 'Руководитель', 'callback_data' => 'verbose_cheef']],
    [['text' => 'Технический специалист', 'callback_data' => 'verbose_techno']],
    [['text' => 'Сервисная компания', 'callback_data' => 'verbose_service_company']],
]));