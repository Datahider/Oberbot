<?php

use losthost\telle\Bot;

use function losthost\Oberbot\mentionByIdArray;
use function losthost\Oberbot\mentionById;

$mention_managers = mentionByIdArray($managers);
$mention_user = mentionById(Bot::$user->id, true);

if (empty($managers)) {
    echo 'Не найдены менеджеры для подтверждения установки срочности задачи.';
} elseif ($group->isManager($user_id)) {
    echo <<<FIN
        $mention_user, вы отметили эту задачу как срочную.

        <b>Предупреждение</b>
        Стоимость выполнения задачи будет рассчитана по повышенному тарифу.
    
        Подтвердите установку срочности для этой задачи.
        FIN;
} else {
    echo <<<FIN
        $mention_user отметил эту задачу как срочную.
            
        <b>Предупреждение</b>
        Стоимость выполнения задачи будет рассчитана по повышенному тарифу.
    
        $mention_managers, подтвердите установку срочности для этой задачи.
        FIN;
}