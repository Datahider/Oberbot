<?php

$icons = [
    'info' => ['ℹ️', 'Информация'],
    'tip' => ['☝️', 'Совет'],
    'done' => ['✅', 'Выполнено'],
    'warning' => ['⚠️', 'Предупреждение'],
    'error' => ['🛑', 'Ошибка'],
    'notification' => ['🛎', 'Уведомление'],
    'reminder' => ['⏰', 'Напоминание'],
];

if (!isset($icons[$type])) {
    $icon = $icons['info'][0];
    $h = $icons['info'][1];
} else {
    $icon = $icons[$type][0];
    $h = $icons[$type][1];
}

if (!$header) {
    echo "$icon <b>$h</b>\n\n$text";
} else {
    echo "$icon $header\n\n$text";
}

