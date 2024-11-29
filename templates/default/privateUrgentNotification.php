<?php

use losthost\Oberbot\view\Emoji;

$url = str_replace('-100', 'c/', "https://t.me/$ticket->chat_id/$ticket->topic_id");

if ($ticket->is_urgent) {
    echo Emoji::ICON_SOS. " Внимание! Срочная заявка: <a href=\"$url\">$ticket->title - #$ticket->id</a>";
} else {
    echo Emoji::ICON_EXCLAMATION. " Возникла неисправность: <a href=\"$url\">$ticket->title - #$ticket->id</a>";
}

