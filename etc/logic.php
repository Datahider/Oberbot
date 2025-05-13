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
use losthost\Oberbot\view\TicketUnlink;
use losthost\Oberbot\view\NoteCreation;
use losthost\Oberbot\view\PrivateTopicCreating;
use losthost\Oberbot\view\WaitCreating;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\data\topic_user;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\data\note;
use losthost\Oberbot\data\wait;
use losthost\Oberbot\data\private_topic;
use losthost\DB\DBList;

require_once 'bot/functions.php';
require_once 'bot/show.php';

// Предварительная обработка
Bot::addHandler(losthost\Oberbot\controller\pre\ForwardedMessageToGeneral::class);
Bot::addHandler(losthost\Oberbot\controller\pre\AgentLeftTheGroup::class);
Bot::addHandler(\losthost\Oberbot\controller\pre\ForumTopicEdited::class);
//Bot::addHandler(\losthost\Oberbot\controller\pre\ForumTopicEditedByBot::class); <<- возникли проблемы с отображением иконок в клиенте
Bot::addHandler(\losthost\Oberbot\controller\command\CommandDigits::class);
Bot::addHandler(losthost\Oberbot\controller\pre\UpdateLastSeenByMessage::class);
Bot::addHandler(\losthost\Oberbot\controller\pre\ForbidArchivedMessage::class);
Bot::addHandler(\losthost\Oberbot\controller\pre\ForbidArchivedCallback::class);
Bot::addHandler(\losthost\Oberbot\controller\pre\WizardStartWhenChatMember::class);
Bot::addHandler(\losthost\Oberbot\controller\pre\TouchAndLinkByMessage::class);
Bot::addHandler(losthost\Oberbot\controller\pre\UserJoinsTheGroup::class);

// Обработка кнопок
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackAskUrgent::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackCancelUrgent::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackContinue::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackDone::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackHelp::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackHid::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackLink::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackList::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackNotify::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackPause::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackRate::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackReopen::class);
Bot::addHandler(\losthost\Oberbot\controller\callback\CallbackTip::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackToTaskTicket::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackVerbose::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackUrgent::class);
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackUserPriority::class);

// Не найден обработчик кнопки
Bot::addHandler(losthost\Oberbot\controller\callback\CallbackUndefined::class);

// Команды
Bot::addHandler(losthost\Oberbot\controller\command\CommandAgent::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandArchive::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandContinue::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandCreate::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandCustomer::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandFree::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandList::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandHelp::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandHid::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandDel::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandDelist::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandDone::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandManager::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandNotify::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandOff::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandPause::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandPay::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandQueue::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandRemind::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandReopen::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandReport::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandReserve::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandStart::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandSub::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandTake::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandTask::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandUnlink::class);
Bot::addHandler(\losthost\Oberbot\controller\command\CommandUp::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandWait::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandNext::class);
Bot::addHandler(losthost\Oberbot\controller\command\CommandFunnel::class);


// Эти команды обрабатываются в любых чатах, куда добавлен бот
Bot::addHandler(losthost\Oberbot\handlers\CommandReviewHandler::class);
Bot::addHandler(losthost\Oberbot\handlers\CommandMd::class);

// REVIEW - похоже это обработка кнопки присоединения к тикету. Проверить
//Bot::addHandler(CallbackLink::class);

/// Проверюят есть ли у бота админские права в группе
//Bot::addHandler(AdminRightsCheckerMessage::class);
//Bot::addHandler(AdminRightsCheckerCallback::class);

Bot::addHandler(losthost\Oberbot\controller\message\NonCommandPrivateMessage::class);
Bot::addHandler(\losthost\Oberbot\controller\message\NonCommandAgentMessage::class);
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

DB::addTracker(DBEvent::INTRAN_DELETE, [topic_admin::class, topic_user::class], TicketUnlink::class);
DB::addTracker(DBEvent::AFTER_INSERT, note::class, NoteCreation::class);

DB::addTracker(DBEvent::AFTER_INSERT, private_topic::class, PrivateTopicCreating::class);
DB::addTracker(DBEvent::AFTER_INSERT, wait::class, WaitCreating::class);

Bot::param('workers_count', 1);

$modify = new DBList(ticket::class, 'wait_for IS NOT NULL', []);

while ($ticket = $modify->next()) {
    $wait = new wait(['task_id' => $ticket->id, 'subtask_id' => $ticket->wait_for], true);
    DB::beginTransaction();
    try {
        if ($wait->isNew()) {
            $wait->write();
        }
        $ticket->wait_for = null;
        $ticket->write();
    } catch (\Exception $exc) {
        DB::rollBack();
        Bot::logException($exc);
    }
    DB::commit();
}

\losthost\Oberbot\makePrivateCommands();
\losthost\Oberbot\makeGroupCommands();
losthost\Oberbot\makeAllAgentsCommands();

