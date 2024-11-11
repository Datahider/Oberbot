<?php

use losthost\Oberbot\view\Emoji;

// Иконки заявок
//     +----- тип (0)-заявка, (1)-задача
//     |  +-- срочность (0)-обычная, (1)-срочная 
//     v  v
$emoji[false][false] = Emoji::ICON_LIFEBUOY;
$emoji[false][true] = Emoji::ICON_SOS;
$emoji[true][false] = Emoji::TASK_REGULAR;
$emoji[true][true] = Emoji::TASK_PRIORITY;

$ticket_type = $ticket->is_task ? 'заявка' : 'задача';
$in_group = $working_group ? " в группе <b>$working_group</b>" : '';
$url = str_replace('-100', 'c/', "https://t.me/$ticket->chat_id/$ticket->topic_id");

echo "Следующая активная $ticket_type$in_group:\n<a href=\"$url\">$ticket->title - #$ticket->id</a>";