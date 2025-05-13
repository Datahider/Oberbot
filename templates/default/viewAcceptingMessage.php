<?php

use losthost\Oberbot\service\Service;
use losthost\Oberbot\view\Emoji;
use losthost\Oberbot\data\ticket;

do {
    $queue_len = 0;
    $time_est = 0;
        
    $user_priority[1] = Emoji::ICON_1;
    $user_priority[2] = Emoji::ICON_2;
    $user_priority[3] = Emoji::ICON_3;
    $user_priority[4] = Emoji::ICON_4;
    $user_priority[5] = Emoji::ICON_5;
    
    $emoji[ticket::TYPE_REGULAR_TASK] = Emoji::TASK_REGULAR;
    $emoji[ticket::TYPE_PRIORITY_TASK] = Emoji::TASK_PRIORITY;
    $emoji[ticket::TYPE_MALFUNCTION] = Emoji::ICON_EXCLAMATION;
    $emoji[ticket::TYPE_SCHEDULED_CONSULT] = Emoji::ICON_CONSULT;
    $emoji[ticket::TYPE_URGENT_CONSULT] = Emoji::ICON_URGENT_CONSULT;
    $emoji[ticket::TYPE_MALFUNCTION_MULTIUSER] = Emoji::ICON_EXCLAMATION_2;
    $emoji[ticket::TYPE_MALFUNCTION_FREE] = Emoji::ICON_SOS;
    
    $title[ticket::TYPE_REGULAR_TASK] = '<b>Задача</b>';
    $title[ticket::TYPE_PRIORITY_TASK] = '<b>Срочная задача</b> (повышенный тариф)';
    $title[ticket::TYPE_MALFUNCTION] = '<b>Неисправность</b>';
    $title[ticket::TYPE_SCHEDULED_CONSULT] = '<b>Личная консультация</b>';
    $title[ticket::TYPE_URGENT_CONSULT] = '<b>Срочная консультация</b> (повышенный тариф)';
    $title[ticket::TYPE_MALFUNCTION_MULTIUSER] = '<b>Неисправность затрагивающая нескольких пользователей</b>';
    $title[ticket::TYPE_MALFUNCTION_FREE] = '<b>Неисправность в предоставляемых услугах</b>';

    $priority_text[ticket::TYPE_REGULAR_TASK] = 'Если вы сообщаете о неисправности, нажмите '. Emoji::ICON_LIFEBUOY. "\nЕсли задача является срочной, нажмите ". Emoji::TASK_PRIORITY;
    
    $task_text[false][false] = "";
    $task_text[false][true] = "";
    $task_text[true][false] = "";
    $task_text[true][true] = "";
    
    $header = $emoji[$ticket->type]. ' '. $title[$ticket->type];
    $priority_footer = isset($priority_text[$ticket->type]) ? $priority_text[$ticket->type] : '';
    if ($priority_footer) {
        $priority_footer .= "\n(<a href=\"https://oberdesk.ru/for-users/what-to-choose/\">что выбрать?</a>)\n\n";
    }
    
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
Очередность выполнения: <?= $user_priority[$ticket->user_priority]; ?> 

Назначенные агенты: <b><?= Service::mentionByIdArray($ticket->getAgents(), '-'); ?></b>
Затраченное время: <b><?= $ticket->getTimeElapsed()->format('%H:%I:%S'); ?></b>
Пользователи: <b><?= Service::mentionByIdArray($ticket->getCustomers(), '-'); ?></b>

Другие пользователи могут присоединиться к заявке нажав ➕

<?= $priority_footer; ?>Нажмите /done, если заявка потеряла актуальность.

<b>Для агентов:</b>
/take /free
