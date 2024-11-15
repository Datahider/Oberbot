<?php

use losthost\telle\Bot;

// data
use losthost\Oberbot\data\chat_group;
use losthost\Oberbot\data\note;
use losthost\Oberbot\data\note_mention;
use losthost\Oberbot\data\session;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\data\topic_user;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\data\user_chat_role;
use losthost\Oberbot\data\accepting_message;
use losthost\timetracker\Timer;
use losthost\timetracker\TimerEvent;
use losthost\timetracker\TagBinder;

require_once 'vendor/autoload.php';

Bot::setup();

chat_group::initDataStructure();
note::initDataStructure();
note_mention::initDataStructure();
session::initDataStructure();
topic::initDataStructure();
topic_admin::initDataStructure();
topic_user::initDataStructure();
chat::initDataStructure();
user_chat_role::initDataStructure();
accepting_message::initDataStructure();
Timer::initDataStructure();
TimerEvent::initDataStructure();
TagBinder::initDataStructure();
losthost\ProxyMessage\message_map::initDataStructure();

Bot::run();