<?php

use function \losthost\Oberbot\__;

if (!empty($tip_text)) {
    echo $tip_text;
} elseif (!empty($tip_name)) {
    echo __($tip_name);
}
