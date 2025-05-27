<?php

use function \losthost\Oberbot\barIndicator;
use losthost\Oberbot\data\topic;

$totals = [
    'total' => 0,
    topic::TYPE_REGULAR_TASK => 0,
    topic::TYPE_PRIORITY_TASK => 0,
    topic::TYPE_MALFUNCTION => 0,
    topic::TYPE_SCHEDULED_CONSULT => 0,
    topic::TYPE_URGENT_CONSULT => 0,
    topic::TYPE_MALFUNCTION_MULTIUSER => 0,
    topic::TYPE_MALFUNCTION_FREE => 0,
    topic::TYPE_BOT_SUPPORT => 0,
    topic::TYPE_PRIVATE_SUPPORT => 0,
    'none' => 0
];

$count = [
    'total' => 0,
    topic::TYPE_REGULAR_TASK => 0,
    topic::TYPE_PRIORITY_TASK => 0,
    topic::TYPE_MALFUNCTION => 0,
    topic::TYPE_SCHEDULED_CONSULT => 0,
    topic::TYPE_URGENT_CONSULT => 0,
    topic::TYPE_MALFUNCTION_MULTIUSER => 0,
    topic::TYPE_MALFUNCTION_FREE => 0,
    topic::TYPE_BOT_SUPPORT => 0,
    topic::TYPE_PRIVATE_SUPPORT => 0,
    'none' => 0
];

$types = [
    topic::TYPE_REGULAR_TASK => 'Задачи',
    topic::TYPE_PRIORITY_TASK => 'Срочные задачи',
    topic::TYPE_MALFUNCTION => 'Неисправности',
    topic::TYPE_SCHEDULED_CONSULT => 'Личные консультации',
    topic::TYPE_URGENT_CONSULT => 'Срочные консультации',
    topic::TYPE_MALFUNCTION_MULTIUSER => 'Неисправности, затрагивающие многих пользователей',
    topic::TYPE_MALFUNCTION_FREE => 'Неисправности в предоставляемых услугах',
    topic::TYPE_BOT_SUPPORT => 'Запросы поддержки бота',
    topic::TYPE_PRIVATE_SUPPORT => 'Запросы поддержки бота в ЛС',
];

$text_report = '';
$the_other = 0;

foreach ($report as $line) {
    $totals['total'] += $line->total_seconds;
    $totals[constant('losthost\\Oberbot\\data\\topic::'. $line->type)] += $line->total_seconds;
    $count['total']++;
    $count[constant('losthost\\Oberbot\\data\\topic::'. $line->type)]++;
    
    $bar = barIndicator($line->total_seconds, $report[0]->total_seconds);
    $h = floor($line->total_seconds/3600);
    $m = round(($line->total_seconds-$h*3600) / 60);
    $time = sprintf("%02d:%02d", $h, $m);
    if (mb_strlen($text_report) < 3072) {
        $text_report .= "{$line->topic_title}\n<code>{$time} $bar</code>\n";
    } else {
        $the_other += $line->total_seconds;
    }
}

$h = floor($totals['total']/3600);
$m = round(($totals['total']-$h*3600) / 60);
$time = sprintf("%02d:%02d", $h, $m);
$float_time = sprintf("%5.3f", $totals['total']/3600);

$begin = date_create_immutable($params['period_start'])->format('d-m-Y');
$end = date_create_immutable($params['period_end'])->add(date_interval_create_from_date_string('1 second ago'))->format('d-m-Y');
echo "<b>Период:</b> \n<u>$begin - $end</u>\n\n";
echo "Всего времени: <b>$time</b> ($float_time ч.)\n\n";

$newline = '';
foreach ($types as $key=>$value) {
    if ($totals[$key]) {
        $h = floor($totals[$key]/3600);
        $m = round(($totals[$key]-$h*3600) / 60);
        $time = sprintf("%02d:%02d", $h, $m);
        $float_time = sprintf("%5.3f", $totals[$key]/3600);
        $c = $count[$key];
        
        echo "<u>$value:</u> <b>$c</b> шт, <b>$time</b> ($float_time ч.)\n";
        $newline = "\n";
    }
}
echo $newline;
echo $text_report;

if ($the_other) {
    $h = floor($the_other/3600);
    $m = round(($the_other-$h*3600) / 60);
    $time = sprintf("%02d:%02d", $h, $m);
    $bar = barIndicator($the_other, $report[0]->total_seconds);
    echo "Другие\n<code>$time $bar</code>\n";
}

// $summ = number_format($total/3600*2500, 2, '.', ' ');
// echo "\nИтого: <b>$summ</b> руб.";