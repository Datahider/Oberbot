<?php

use losthost\Oberbot\view\Emoji;

$icon_is_forum = $is_forum ? Emoji::ICON_DONE : Emoji::ICON_TODO;
$icon_is_admin = $is_admin ? Emoji::ICON_DONE : Emoji::ICON_TODO;

?>

Привет! Я <b>Oberbot</b>. Я могу помогать специалистам осуществлять техподдержку пользователей в этой группе.
Для этого группа должна быть настроена. Проверяю:

<?= $icon_is_forum; ?> Включите темы
<?= $icon_is_admin; ?> Назначьте меня админом




