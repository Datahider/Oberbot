<?php

$translation['Тест перевода на %s.'] = 'Test translation to %s.';
$translation['Дайте права админа!'] = 'Для нормальной работы мне нужны права администратора в этом чате.';
$translation['Заявка в процессе создания.'] = '%s эта заявка находится в процессе создания. Переписка в ней запрещена. Ваше сообщение удалено.';

if (isset($translation[$msg_text])) {
    $msg_text = $translation[$msg_text];
}

printf($msg_text, $value);


