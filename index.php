<?php

use losthost\telle\Bot;
use losthost\telle\model\DBBotParam;

/// Трекинг (view)
use losthost\DB\DB;
use losthost\DB\DBEvent;
use losthost\timetracker\TimerEvent;

use losthost\Oberbot\view\TicketCreating;
use losthost\Oberbot\view\TicketUpdating;
use losthost\Oberbot\view\ChatCreateUpdate;

use losthost\Oberbot\view\TimerEventCreated;
use losthost\Oberbot\view\TimerEventUpdated;

use losthost\Oberbot\view\TicketCustomerLink;

// data
use losthost\Oberbot\data\chat_group;
use losthost\Oberbot\data\note;
use losthost\Oberbot\data\session;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\data\topic_user;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\data\user_chat_role;

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

// Предварительная обработка
Bot::addHandler(losthost\Oberbot\controller\pre\WizardStartWhenAdded::class);
Bot::addHandler(losthost\Oberbot\controller\pre\WizardStartWhenChatMember::class);
Bot::addHandler(\losthost\Oberbot\controller\pre\TouchAndLinkByMessage::class);

// Обработка кнопок
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackContinue::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackDone::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackLink::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackNotify::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackPause::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackRate::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackReopen::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackToTaskTicket::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackVerbose::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackUrgent::class);

// Не найден обработчик кнопки
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackUndefined::class);

// Команды
Bot::addHandler(losthost\Oberbot\controller\command\CommandAgent::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandContinue::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandCreate::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandCustomer::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandGroup::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandDone::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandNotify::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandOff::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandPause::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandRemind::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandReopen::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandRun::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandStart::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandStop::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandTake::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandTask::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandUngroup::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandUnlink::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandUrgent::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandWait::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandNext::class);


// Эти команды обрабатываются в любых чатах, куда добавлен бот
Bot::addHandler(losthost\Oberbot\handlers\CommandReviewHandler::class);
Bot::addHandler(losthost\Oberbot\handlers\CommandReportHandler::class);
Bot::addHandler(losthost\Oberbot\handlers\CommandMd::class);

// Этот хендлер не даёт пройти обработке дальше если в chat->process_tickets не true
Bot::addHandler(\losthost\Oberbot\handlers\NonCommandChatCheckerHandler::class);                                

// REVIEW - похоже это обработка кнопки присоединения к тикету. Проверить
//Bot::addHandler(CallbackLink::class);

/// Проверюят есть ли у бота админские права в группе
//Bot::addHandler(AdminRightsCheckerMessage::class);
//Bot::addHandler(AdminRightsCheckerCallback::class);

Bot::addHandler(\losthost\Oberbot\controller\ForumTopicCreatedHandler::class);
Bot::addHandler(losthost\Oberbot\controller\message\FirstTopicMessageHandler::class);
Bot::addHandler(\losthost\Oberbot\controller\message\TicketCloseReopenMessage::class);
Bot::addHandler(\losthost\Oberbot\controller\message\AgentMessage::class);
Bot::addHandler(losthost\Oberbot\controller\message\CustomerMessage::class);

//Bot::addHandler(NonCommandPrivateMessage::class);
//Bot::addHandler(NonCommandAgentMessage::class);

DB::addTracker(DBEvent::AFTER_INSERT, ticket::class, TicketCreating::class);
DB::addTracker(DBEvent::AFTER_UPDATE, ticket::class, TicketUpdating::class);

DB::addTracker(DBEvent::AFTER_INSERT, TimerEvent::class, TimerEventCreated::class);
DB::addTracker(DBEvent::AFTER_UPDATE, TimerEvent::class, TimerEventUpdated::class);

DB::addTracker(DBEvent::AFTER_INSERT, topic_user::class, TicketCustomerLink::class);

DB::addTracker([DBEvent::AFTER_INSERT, DBEvent::AFTER_UPDATE], chat::class, ChatCreateUpdate::class);

$bot_username = new DBBotParam('bot_username');
$bot_userid = new DBBotParam('bot_userid');

$data = Bot::$api->getMe();
$bot_username->value = $data->getUsername();
$bot_userid->value = $data->getId();

Bot::run();