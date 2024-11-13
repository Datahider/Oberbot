<?php

use losthost\Oberbot\service\Service;

if ($user_banned) {
    echo "Ваше сообщение не соответствует теме заявки. Вы заблокированы на $user_banned минут.\n\n"; 
}

if ($ticket_created) {
    $ticket_mention = Service::ticketMention($ticket_created);
    echo "По вашему сообщению создана новая задача $ticket_mention";
}
