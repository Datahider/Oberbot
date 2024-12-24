<b>Подготовка оплаты</b>

В группах, где вы являетесь администратором найдены следующие агенты:

<?php

foreach ($agents as $agent) {
    $name = trim("$agent->first_name $agent->last_name");
    $text = "- <a href=\"tg://user?id=$agent->id\">$name</a>";
    if ($agent->username) {
        $text .= " (@$agent->username)";
    }
    if (is_null($agent->paid_till)) {
        $paid_till = '<u><i>Не оплачен</i></u>';
    } else {
        $paid_till = "<u><i>Оплачен до <b>$agent->paid_till</b></i></u>";
    }
    echo "$text\n$paid_till\n\n";
}
