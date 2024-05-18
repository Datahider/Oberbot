<?php

function barIndicator($value, $max_value=100, $max_bars=30) {
    $bars_symbols = ['█', '▌', '▏'];
    
    $one_bar_value = $max_value / $max_bars;
    $bars = round($value / $one_bar_value);

    if ($bars == 0) {
        return $bars_symbols[2];
    } elseif ($bars % 2) {
        return str_repeat($bars_symbols[0], ($bars-1)/2). $bars_symbols[1];
    } else {
        return str_repeat($bars_symbols[0], $bars/2);
    }
}
