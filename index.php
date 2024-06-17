<?php

use losthost\telle\Bot;

// data
use losthost\Oberbot\data\chat_group;
use losthost\Oberbot\data\note;
use losthost\Oberbot\data\session;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\data\topic_user;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\data\user_chat_role;

use losthost\Oberbot\handlers\CallbackLink;

use losthost\Oberbot\handlers\CommandReviewHandler;
use losthost\Oberbot\handlers\CommandReportHandler;
use losthost\Oberbot\handlers\CommandMd;
use losthost\Oberbot\handlers\CommandAgent;
use losthost\Oberbot\handlers\CommandCustomer;
use losthost\Oberbot\handlers\CommandStart;

use losthost\Oberbot\handlers\ForumTopicCreatedHandler;
use losthost\Oberbot\handlers\FirstTopicMessageHandler;
use losthost\Oberbot\handlers\NonCommandChatCheckerHandler;
use losthost\Oberbot\handlers\NonCommandPrivateMessage;
use losthost\Oberbot\handlers\NonCommandAgentMessage;

use losthost\Oberbot\data\message_map;

require_once 'vendor/autoload.php';
require_once 'bot/functions.php';
require_once 'bot/show.php';

Bot::setup();

chat_group::initDataStructure();
note::initDataStructure();
session::initDataStructure();
topic::initDataStructure();
topic_admin::initDataStructure();
topic_user::initDataStructure();
chat::initDataStructure();
user_chat_role::initDataStructure();

losthost\ProxyMessage\message_map::initDataStructure();

Bot::addHandler(CallbackLink::class);

Bot::addHandler(CommandReviewHandler::class);
Bot::addHandler(CommandReportHandler::class);
Bot::addHandler(CommandMd::class);
Bot::addHandler(CommandAgent::class);
Bot::addHandler(CommandCustomer::class);
Bot::addHandler(CommandStart::class);

Bot::addHandler(NonCommandChatCheckerHandler::class);
Bot::addHandler(ForumTopicCreatedHandler::class);
Bot::addHandler(FirstTopicMessageHandler::class);
Bot::addHandler(NonCommandPrivateMessage::class);
Bot::addHandler(NonCommandAgentMessage::class);

Bot::run();