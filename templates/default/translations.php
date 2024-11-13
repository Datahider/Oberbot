<?php

$translations = [
    "Can't link ticket's agent as a customer" => 'Не могу присоединить вас как пользователя, т.к. вы являетесь исполнителем по этой заявке. Используйте /unlink',
    'Customer is already linked.' => 'Вы уже присоединились.',
    '%s, you are not allowed to run this command.' => '%s, вы не можете использовать эту команду.',
    'вы присоединились к заявке.' => "вы присоединились к заявке.\n\nИспользуйте /unlink для отсоединения.",
    
];

if (empty($translations[$text])) {
    echo $text;
} else {
    echo $translations[$text];
}