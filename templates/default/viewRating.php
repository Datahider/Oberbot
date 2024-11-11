<?php

use losthost\Oberbot\view\Emoji;

$score[-1] = Emoji::RATING_BAD. ' <b>Плохо</b>';
$score[0] = Emoji::RATING_ACCEPTABLE. ' <b>Приемлемо</b>';
$score[1] = Emoji::RATING_GOOD. ' <b>Хорошо</b>';

?>

✅ <b>Выполнено</b>

Заявка закрыта. Оценка пользователя: <?= $score[$ticket->score]; ?>
