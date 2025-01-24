<?php

use function \losthost\Oberbot\barIndicator;

$total = 0;
$text_report = '';
$the_other = 0;

foreach ($report as $line) {
    $total += $line->total_seconds;
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

$h = floor($total/3600);
$m = round(($total-$h*3600) / 60);
$time = sprintf("%02d:%02d", $h, $m);
$float_time = sprintf("%5.3f", $total/3600);

$begin = date_create_immutable($params['period_start'])->format('d-m-Y');
$end = date_create_immutable($params['period_end'])->add(date_interval_create_from_date_string('1 second ago'))->format('d-m-Y');
echo "<b>Период:</b> \n<u>$begin - $end</u>\n\n";
echo "Всего времени: <b>$time</b> ($float_time ч.)\n\n";

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