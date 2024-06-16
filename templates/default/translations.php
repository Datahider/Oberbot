<?php

$translations = [
    
];

if (empty($translations[$text])) {
    echo $text;
} else {
    echo $translations[$text];
}