<?php

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use function \losthost\Oberbot\__;

$keyboard = [[['text' => __('all'), 'callback_data' => 'list_']]];

if (!empty($agent_lists)) {
    foreach ($agent_lists as $list) {
        $prefix = $list == $active_list ? '⚙️ ' : '';
        $keyboard[] = [['text' => "$prefix$list", 'callback_data' => "list_$list"]];
    }
}

echo serialize(new InlineKeyboardMarkup($keyboard));
