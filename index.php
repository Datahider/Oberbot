<?php

use losthost\telle\Bot;
use losthost\telle\model\DBBotParam;

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

use losthost\Oberbot\handlers\NonCommandChatCheckerHandler;

////////////////////////////////////////////////////////////////////////////////////////
/// Новые обработчики из папки controller                                         /////
use losthost\Oberbot\controller\AdminRightsCheckerMessage;
use losthost\Oberbot\controller\ForumTopicCreatedHandler;                        /////
use losthost\Oberbot\controller\FirstTopicMessageHandler;                       /////
////////////////////////////////////////////////////////////////////////////////////

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

Bot::addHandler(AdminRightsCheckerMessage::class);

Bot::addHandler(CallbackLink::class);

Bot::addHandler(CommandReviewHandler::class);
Bot::addHandler(CommandReportHandler::class);
Bot::addHandler(CommandMd::class);
Bot::addHandler(CommandAgent::class);
Bot::addHandler(CommandCustomer::class);
Bot::addHandler(CommandStart::class);

////////////////////////////////////////////////////////////////////////////////////////////
// Этот хендлер не даёт пройти обработке дальше если в chat->process_tickets не true  /////
Bot::addHandler(NonCommandChatCheckerHandler::class);                                /////
/////////////////////////////////////////////////////////////////////////////////////////

Bot::addHandler(ForumTopicCreatedHandler::class);
Bot::addHandler(FirstTopicMessageHandler::class);
Bot::addHandler(NonCommandPrivateMessage::class);
Bot::addHandler(NonCommandAgentMessage::class);

$bot_username = new DBBotParam('bot_username');
$bot_userid = new DBBotParam('bot_userid');

$data = Bot::$api->getMe();
$bot_username->value = $data->getUsername();
$bot_userid->value = $data->getId();

Bot::run();