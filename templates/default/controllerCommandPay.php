<b>Подготовка оплаты</b>

В группах, где вы являетесь администратором найдены следующие агенты:

<?php
use losthost\DB\DBView;
use function losthost\Oberbot\__;

$users = new DBView(__('SELECT id, first_name, last_name, username FROM [telle_users] WHERE id IN (%agent_ids%)', ['agent_ids' => implode(',', $agent_ids)]));

while ($users->next()) {
    $name = trim("$users->first_name $users->last_name");
    $text = "- <a href=\"tg://user?id=$users->id\">$name</a>";
    if ($users->username) {
        $text .= " (@$users->username)";
    }
    echo "$text\n";
}
