<?php

use losthost\telle\Bot;
use losthost\telle\model\DBBotParam;

/// Трекинг (view)
use losthost\DB\DB;
use losthost\DB\DBEvent;
use losthost\Oberbot\data\ticket;
use losthost\timetracker\TimerEvent;

use losthost\Oberbot\view\TicketCreating;
use losthost\Oberbot\view\TicketAccepting;
use losthost\Oberbot\view\TicketClosing;

use losthost\Oberbot\view\TimerEventCreated;
use losthost\Oberbot\view\TimerEventUpdated;

// data
use losthost\Oberbot\data\chat_group;
use losthost\Oberbot\data\note;
use losthost\Oberbot\data\session;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\data\topic_user;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\data\user_chat_role;

//use losthost\Oberbot\handlers\CallbackLink;
//
//use losthost\Oberbot\handlers\CommandReviewHandler;
//use losthost\Oberbot\handlers\CommandReportHandler;
//use losthost\Oberbot\handlers\CommandMd;
//use losthost\Oberbot\handlers\CommandAgent;
//use losthost\Oberbot\handlers\CommandCustomer;
//use losthost\Oberbot\handlers\CommandStart;

// Private chat commands
use losthost\Oberbot\controller\CommandStart;

use losthost\Oberbot\controller\CommandTake;
use losthost\Oberbot\controller\CommandContinue;

//use losthost\Oberbot\handlers\NonCommandChatCheckerHandler;

use losthost\Oberbot\controller\AdminRightsCheckerMessage;
use losthost\Oberbot\controller\AdminRightsCheckerCallback;

use losthost\Oberbot\controller\ForumTopicCreatedHandler;

use losthost\Oberbot\controller\TouchAndLinkByMessage;
use losthost\Oberbot\controller\FirstTopicMessageHandler;

use losthost\Oberbot\controller\CommandNotify;
use losthost\Oberbot\controller\CommandPause;
use losthost\Oberbot\controller\CommandDone;

//use losthost\Oberbot\handlers\NonCommandPrivateMessage;
//use losthost\Oberbot\handlers\NonCommandAgentMessage;

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

// Обработка кнопок
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackVerbose::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackUndefined::class);

// Команды в приватном чате
Bot::addHandler(CommandStart::class);

// Эти команды обрабатываются в любых чатах, куда добавлен бот
Bot::addHandler(CommandReviewHandler::class);
Bot::addHandler(CommandReportHandler::class);
Bot::addHandler(CommandMd::class);
Bot::addHandler(CommandAgent::class);
Bot::addHandler(CommandCustomer::class);
Bot::addHandler(CommandTake::class);
Bot::addHandler(CommandContinue::class);

// Этот хендлер не даёт пройти обработке дальше если в chat->process_tickets не true
Bot::addHandler(NonCommandChatCheckerHandler::class);                                

// REVIEW - похоже это обработка кнопки присоединения к тикету. Проверить
Bot::addHandler(CallbackLink::class);

/// Проверюят есть ли у бота админские права в группе
//Bot::addHandler(AdminRightsCheckerMessage::class);
//Bot::addHandler(AdminRightsCheckerCallback::class);

Bot::addHandler(ForumTopicCreatedHandler::class);
Bot::addHandler(TouchAndLinkByMessage::class);
Bot::addHandler(FirstTopicMessageHandler::class);

Bot::addHandler(CommandNotify::class);
Bot::addHandler(CommandPause::class);
Bot::addHandler(CommandDone::class);

Bot::addHandler(NonCommandPrivateMessage::class);
Bot::addHandler(NonCommandAgentMessage::class);

DB::addTracker(DBEvent::AFTER_INSERT, ticket::class, TicketCreating::class);
DB::addTracker(DBEvent::AFTER_UPDATE, ticket::class, TicketAccepting::class);
DB::addTracker(DBEvent::AFTER_UPDATE, ticket::class, TicketClosing::class);

DB::addTracker(DBEvent::AFTER_INSERT, TimerEvent::class, TimerEventCreated::class);
DB::addTracker(DBEvent::AFTER_UPDATE, TimerEvent::class, TimerEventUpdated::class);

$bot_username = new DBBotParam('bot_username');
$bot_userid = new DBBotParam('bot_userid');

$data = Bot::$api->getMe();
$bot_username->value = $data->getUsername();
$bot_userid->value = $data->getId();

Bot::run();