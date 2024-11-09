<?php

use function losthost\Oberbot\getQueueLen;
use function losthost\Oberbot\mentionByIdArray;
use function losthost\Oberbot\getTimeEst;
use losthost\Oberbot\view\Emoji;

do {
    $queue_len = getQueueLen($ticket->id);
    $time_est = getTimeEst($ticket->id);
    
    // Иконки заявок
    //     +----- тип (0)-заявка, (1)-задача
    //     |  +-- срочность (0)-обычная, (1)-срочная 
    //     v  v
    $emoji[false][false] = Emoji::TICKET_REGULAR;
    $emoji[false][true] = Emoji::TICKET_URGENT;
    $emoji[true][false] = Emoji::TASK_REGULAR;
    $emoji[true][true] = Emoji::TASK_PRIORITY;
    
    $title[false][false] = 'Заявка';
    $title[false][true] = 'Срочная заявка';
    $title[true][false] = 'Задача';
    $title[true][true] = 'Приоритетная задача';
    
    $priority_text[false][false] = 'Если заявка срочная — нажмите '. Emoji::TICKET_URGENT;
    $priority_text[false][true] = 'Для снижания приоритета заявки — нажмите '. Emoji::ACTION_PRIORITY_DOWN;
    $priority_text[true][false] = 'Если задача приоритетная — нажмите '. Emoji::TASK_PRIORITY;
    $priority_text[true][true] = 'Для снижения приоритета задачи — нажмите '. Emoji::ACTION_PRIORITY_DOWN;
    
    $header = $emoji[$ticket->is_task][$ticket->is_urgent]. ' '. $title[$ticket->is_task][$ticket->is_urgent];
    $priority_footer = $priority_text[$ticket->is_task][$ticket->is_urgent];
    
    // формирование правильной формы(падежа) слова заявка для обозначения количества заявок
    switch (substr($queue_len, -1)) {
        case '#':
        case '0':
        case '9':
        case '8':
        case '7':
        case '6':
        case '5':
            $tickets_rus = 'заявок';
            break;
        case '4':
        case '3':
        case '2':
            $tickets_rus = 'заявки';
            break;
        default:
            $tickets_rus = 'заявка';
    }
} while (0); // чисто для удобства чтения
?>

<b><?= $header; ?></b>

Идентификатор: <b>#<?= $ticket->id; ?></b>
Перед вами: <b><?= $queue_len; ?> <?=$tickets_rus;?></b>

Назначенные агенты: <b><?= mentionByIdArray($ticket->getAgents(), '-'); ?></b>
Затраченное время: <b><?= $ticket->getTimeElapsed()->format('%H:%I:%S'); ?></b>
Пользователи: <b><?= mentionByIdArray($ticket->getCustomers(), '-'); ?></b>

Другие пользователи вашей компании могут присоединиться к заявке отправив сообщение или нажав ➕

<?= $priority_footer; ?>️

<b>Для агентов:</b>
/take
