<?php

use losthost\telle\Bot;
use losthost\Oberbot\background\CloseIncompleteTicket;

require 'vendor/autoload.php';

Bot::setup();

//$closer = new CloseIncompleteTicket(22444);
//$closer->run();

//$reminder = new \losthost\Oberbot\background\RemindRunningTimer("22502 203645978");
//$reminder->run();

$stopper = new \losthost\Oberbot\background\StopRunningTimer("22502 203645978");
$stopper->run();

