<?php

use losthost\Oberbot\service\Service;

$lines = explode("\n", $note->note);

if (count($lines) == 1) {
    echo Service::__('Скрытое сообщение от '. Service::mentionById($note->user_id));
} else {
    echo $lines[0]. Service::__(" от "). Service::mentionById($note->user_id);
}

