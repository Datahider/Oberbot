<?php

use losthost\Oberbot\service\Service;

echo '✍️ Опишите проделанную работу в '. Service::ticketMention($ticket);

if ($pending) {
    echo "\n\n❗️ <b>Неисправность!</b>\n\n". Service::ticketMention($pending);
}