<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

use losthost\Oberbot\view\Emoji;

$line[] = ['text' => '➕', 'callback_data' => 'link'];
if ($ticket->is_task && !$ticket->is_urgent) {
    $line[] = ['text' => Emoji::ICON_LIFEBUOY, 'to_ticket'];
    $line[] = ['text' => Emoji::ACTION_PRIORITY_UP, 'callback_data' => 'urgent'];
} elseif ($ticket->is_task && $ticket->is_urgent) {
    $line[] = ['text' => Emoji::ICON_LIFEBUOY, 'to_ticket'];
    $line[] = ['text' => Emoji::ACTION_PRIORITY_UP, 'callback_data' => 'urgent'];
} elseif (!$ticket->is_task && !$ticket->is_urgent) {
    $line[] = ['text' => Emoji::ICON_LIFEBUOY, 'to_ticket'];
    $line[] = ['text' => Emoji::ACTION_PRIORITY_UP, 'callback_data' => 'urgent'];
} else {
    $line[] = ['text' => Emoji::ACTION_PRIORITY_UP, 'to_ticket'];
    $line[] = ['text' => Emoji::ACTION_PRIORITY_UP, 'callback_data' => 'urgent'];
}

echo serialize(new InlineKeyboardMarkup([
    $line
]));