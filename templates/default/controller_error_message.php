<?php

$translation['Тест перевода на %s.'] = 'Test translation to %s.';

if (is_set($translation[$template])) {
    $template = $translation[$template];
}

printf($template, ...$values);


