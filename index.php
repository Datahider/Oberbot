<?php

use losthost\telle\Bot;

use losthost\Oberbot\handlers\CommandReviewHandler;
use losthost\Oberbot\handlers\CommandReportHandler;
use losthost\Oberbot\handlers\CommandMd;

require_once 'vendor/autoload.php';
require_once 'bot/functions.php';

Bot::setup();

Bot::addHandler(CommandReviewHandler::class);
Bot::addHandler(CommandReportHandler::class);
Bot::addHandler(CommandMd::class);

Bot::run();