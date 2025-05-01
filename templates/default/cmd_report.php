<?php

use function \losthost\Oberbot\barIndicator;

$totals = [
    'total' => 0,
    'task' => 0,
    'malfunction' => 0,
    'urgent' => 0,
    'none' => 0
];

$types = [
    'malfunction' => 'Неисправности',
    'task' => 'Задачи',
    'urgent' => 'Срочные'
];

$text_report = '';
$the_other = 0;

foreach ($report as $line) {
    $totals['total'] += $line->total_seconds;
    $totals[$line->type] += $line->total_seconds;
    
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
        
        echo "$value: <b>$time</b> ($float_time ч.)\n";
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