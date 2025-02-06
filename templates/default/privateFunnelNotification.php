<?php

use losthost\Oberbot\view\Emoji;

$url = str_replace('-100', 'c/', "https://t.me/$ticket->chat_id/$ticket->topic_id");

echo Emoji::ICON_FUNNEL. " Внимание! Новый клиент: <a href=\"$url\">$ticket->title - #$ticket->id</a>";

