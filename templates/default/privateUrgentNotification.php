<?php

use losthost\Oberbot\view\Emoji;

$url = str_replace('-100', 'c/', "https://t.me/$ticket->chat_id/$ticket->topic_id");

echo Emoji::ICON_SOS. " Появилась новая срочная заявка: <a href=\"$url\">$ticket->title - #$ticket->id</a>";

