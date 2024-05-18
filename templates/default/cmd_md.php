<?php

$text = '';

$text .= '# Открытые заявки и задачи';
if ($working_group != 'all') {
    $text .= " по группе $working_group";
}
$text .= "\n";

$is_task = null;

foreach ($result as $row) {
    if ($row['is_task'] !== $is_task) {
        $is_task = $row['is_task'];
        $text .= '# '. ($is_task == 0 ? 'Заявки' : 'Задачи');
        $text .= "\n";
        $status = null;
    }
    
    if ($row['status'] !== $status) {
        $status = $row['status'];
        $text .= '## '. ($status == 0 ? 'Новые' : 'Текущие');
        $text .= "\n";
        $is_urgent = null;
    }
    
    if ($row['is_urgent'] !== $is_urgent) {
        $is_urgent = $row['is_urgent'];
        $text .= '### '. ($is_urgent == 1 ? 'Срочные' : 'Обычные');
        $text .= "\n";
    }
    
    $link_id = substr($row['chat_id'], 4);
    $text .= "$row[chat_title] - [$row[topic_title]](tg://privatepost?channel=$link_id&post=$row[topic_id])\n";
}

echo $text;