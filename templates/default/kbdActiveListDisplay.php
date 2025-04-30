<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use losthost\Oberbot\view\TicketQueue;
use losthost\telle\Bot;
use function \losthost\Oberbot\__;

$queue_len = TicketQueue::getQueueLen(Bot::$user->id, 'all');
$keyboard = [[['text' => __('all'). " ($queue_len)", 'callback_data' => 'list_']]];

if (!empty($agent_lists)) {
    foreach ($agent_lists as $list) {
        $prefix = $list == $active_list ? '⚙️ ' : '';
        $queue_len = TicketQueue::getQueueLen(Bot::$user->id, $list);
        $keyboard[] = [['text' => "$prefix$list ($queue_len)", 'callback_data' => "list_$list"]];
    }
}

echo serialize(new InlineKeyboardMarkup($keyboard));
