<?php

use losthost\telle\Bot;
use losthost\Oberbot\background\CloseIncompleteTicket;

require 'vendor/autoload.php';

Bot::setup();

$closer = new CloseIncompleteTicket(22444);
$closer->run();
