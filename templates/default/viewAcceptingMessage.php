<?php

use losthost\Oberbot\service\Service;
use losthost\Oberbot\view\Emoji;

do {
    $queue_len = 0;
    $time_est = 0;
    
    // Иконки заявок
    //     +----- тип (0)-заявка, (1)-задача
    //     |  +-- срочность (0)-обычная, (1)-срочная 
    //     v  v
    $emoji[false][false] = Emoji::ICON_LIFEBUOY;
    $emoji[false][true] = Emoji::ICON_SOS;
    $emoji[true][false] = Emoji::TASK_REGULAR;
    $emoji[true][true] = Emoji::TASK_PRIORITY;
    
    $title[false][false] = 'Неисправность';
    $title[false][true] = 'Срочно!';
    $title[true][false] = 'Задача';
    $title[true][true] = 'Приоритетная задача';
    
    $priority_text[false][false] = 'Если неисправность привела к полной остановке работы, нажмите '. Emoji::ICON_SOS;
    $priority_text[false][true] = '<b>Сохраняйте спокойствие. Помощь уже в пути.</b>';
    $priority_text[true][false] = 'Если вы сообщаете о неисправности, нажмите '. Emoji::ICON_LIFEBUOY. "\nЕсли задача является приоритетной, нажмите ". Emoji::TASK_PRIORITY;
    $priority_text[true][true] = 'Для снижения приоритета задачи, нажмите '. Emoji::ACTION_PRIORITY_DOWN;
    
    $task_text[false][false] = "";
    $task_text[false][true] = "";
    $task_text[true][false] = "";
    $task_text[true][true] = "";
    
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
<u><?= Service::ticketMentionNoId($ticket); ?></u>

Идентификатор: <b>#<?= $ticket->id; ?></b>

Назначенные агенты: <b><?= Service::mentionByIdArray($ticket->getAgents(), '-'); ?></b>
Затраченное время: <b><?= $ticket->getTimeElapsed()->format('%H:%I:%S'); ?></b>
Пользователи: <b><?= Service::mentionByIdArray($ticket->getCustomers(), '-'); ?></b>

Другие пользователи могут присоединиться к заявке нажав ➕

<?= $priority_footer; ?>️

<b>Для агентов:</b>
/take
