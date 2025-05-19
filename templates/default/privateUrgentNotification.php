<?php

use losthost\Oberbot\view\Emoji;
use losthost\Oberbot\data\ticket;
use function losthost\Oberbot\__;
use function \losthost\Oberbot\ticketMention;

$url = str_replace('-100', 'c/', "https://t.me/$ticket->chat_id/$ticket->topic_id");

$icon = Emoji::TEXT_EMOJI_BY_TYPE[$ticket->type];
$type_text = lcfirst(__('type_'.$ticket->type));
$ticket_mention = ticketMention($ticket);

if ($ticket->status == ticket::STATUS_NEW) {
    echo "$icon Новая $type_text: $ticket_mention";
} elseif ($ticket->status == ticket::STATUS_USER_ANSWERED) {
    echo "$icon Пользователь ответил в $ticket_mention";
} elseif ($ticket->status == ticket::STATUS_REOPEN) {
    echo "$icon Переоткрыта $type_text $ticket_mention";
} else {
    echo "Глюк уведомления: $ticket_mention";
}

