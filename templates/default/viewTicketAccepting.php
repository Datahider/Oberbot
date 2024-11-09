<?php

isset($queue_len) || $queue_len = '###';
isset($ticket_id) || $ticket_id = '###';
isset($time_est)  || $time_est = '###';
isset($mention) || $mention = 'Уважаемый пользователь';

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
?>

ℹ️ Информация

<?=$mention;?>, вашей заявке присвоен номер #<?=$ticket_id;?>. Перед вами в очереди: <?=$queue_len;?> <?=$tickets_rus;?>. Ориентировочное время ожидания ответа: <?=$time_est;?>.

Другие пользователи вашей компании могут присоединиться к заявке отправив сообщение или нажав ➕

Если заявка срочная — нажмите ‼️

<blockquote><b>Для агентов:</b>
/go</blockquote>
