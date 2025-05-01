<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

use losthost\Oberbot\view\Emoji;

$line[] = ['text' => '➕', 'callback_data' => 'link'];
if ($ticket->is_task && !$ticket->is_urgent) {
    $line[] = ['text' => Emoji::ICON_LIFEBUOY, 'callback_data' => 'to_ticket'];
    $line[] = ['text' => Emoji::TASK_PRIORITY, 'callback_data' => 'ask_urgent'];
} elseif ($ticket->is_task && $ticket->is_urgent) {
    // $line[] = ['text' => Emoji::ACTION_PRIORITY_DOWN, 'callback_data' => 'urgent_off'];
} elseif (!$ticket->is_task && !$ticket->is_urgent) {
    // $line[] = ['text' => Emoji::ICON_SOS, 'callback_data' => 'urgent'];
}

$user_priority = [
    ['text' => $ticket->user_priority == 1 ? Emoji::ICON_1 : '1', 'callback_data' => 'user_priority_1'],
    ['text' => $ticket->user_priority == 2 ? Emoji::ICON_2 : '2', 'callback_data' => 'user_priority_2'],
    ['text' => $ticket->user_priority == 3 ? Emoji::ICON_3 : '3', 'callback_data' => 'user_priority_3'],
    ['text' => $ticket->user_priority == 4 ? Emoji::ICON_4 : '4', 'callback_data' => 'user_priority_4'],
    ['text' => $ticket->user_priority == 5 ? Emoji::ICON_5 : '5', 'callback_data' => 'user_priority_5'],
];

echo serialize(new InlineKeyboardMarkup([
    $user_priority, $line
]));