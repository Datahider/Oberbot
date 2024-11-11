<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

use losthost\Oberbot\view\Emoji;

$line[] = ['text' => '➕', 'callback_data' => 'link'];
if ($ticket->is_task && !$ticket->is_urgent) {
    $line[] = ['text' => Emoji::ICON_LIFEBUOY, 'callback_data' => 'to_ticket'];
    $line[] = ['text' => Emoji::TASK_PRIORITY, 'callback_data' => 'urgent'];
} elseif ($ticket->is_task && $ticket->is_urgent) {
    $line[] = ['text' => Emoji::ACTION_PRIORITY_DOWN, 'callback_data' => 'urgent_off'];
} elseif (!$ticket->is_task && !$ticket->is_urgent) {
    $line[] = ['text' => Emoji::ICON_SOS, 'callback_data' => 'urgent'];
}

echo serialize(new InlineKeyboardMarkup([
    $line
]));