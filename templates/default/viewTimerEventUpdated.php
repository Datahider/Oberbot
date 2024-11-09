<?php 

use losthost\Oberbot\view\Emoji;

?>
<?= Emoji::ACTION_PAUSE; ?> <b>Пауза</b>

<?= $mention; ?> приостановил работу. 
Учтено времени: <b><?= $duration->format('%H:%I:%S'); ?></b>
Всего затрачено: <b><?= $ticket_time_elapsed->format('%H:%I:%S'); ?></b>

<b>Для агентов:</b>
/continue /notify /done
