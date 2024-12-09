<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\ticket;
use losthost\DB\DBEvent;
use losthost\Oberbot\view\Emoji;
use losthost\DB\DBView;
use losthost\Oberbot\background\CloseIncompleteTicket;
use losthost\telle\model\DBPendingJob;
use losthost\timetracker\Timer;
use losthost\Oberbot\data\private_topic;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionByIdArray;

class TicketUpdating extends DBTracker {
    
    protected DBEvent $event;
    protected ticket $ticket;
    
    public function track(\losthost\DB\DBEvent $event) {
        
        $this->ticket = $event->object;
        $this->event = $event;
        
        $this->destroyIncompleteTimer();
        $this->notifyTypeChange();
        $this->notifyPriorityChange();
        $this->notifyStatusChanging();
        $this->notifyUrgentTicket();

        // Creating/updating ticket info message
        $accepting_message = new AcceptingMessage($this->ticket);
        $accepting_message->show();
        
    }
    
    protected function destroyIncompleteTimer() {

        if (array_search('status', $this->event->fields) === false || $this->ticket->status == ticket::STATUS_CREATING) {
            return;
        }

        $job_id = new DBView(
                'SELECT id AS value FROM [telle_pending_jobs] WHERE job_class = ? AND job_args = ?', 
                [CloseIncompleteTicket::class, $this->ticket->id]);

        if ($job_id->next()) {
            $job = new DBPendingJob($job_id->value);
            $job->delete();
            Bot::logComment("Pending job id:$job_id->value is deleted.");
        }
        
    }
    
    protected function notifyUrgentTicket() {
        if (array_search('is_urgent', $this->event->fields) === false 
                && array_search('is_task', $this->event->fields) === false 
                && array_search('status', $this->event->fields) === false) {
            return;
        }
        
        $skip_statuses = [
            ticket::STATUS_CLOSED,
            ticket::STATUS_ARCHIVED,
            ticket::STATUS_IN_PROGRESS,
            ticket::STATUS_AWAITING_USER
        ];
        if ( $this->ticket->is_task || array_search($this->ticket->status,  $skip_statuses) !== false ) {
            return;
        }
        
        // TODO -- доставать так же language_code
        if ($this->ticket->is_urgent) {
            $agent = new DBView('SELECT user_id AS id FROM [user_chat_role] WHERE role = "agent" AND chat_id = ?', [$this->ticket->chat_id]);
        } else {
            $agent = new DBView('SELECT user_id AS id FROM [user_chat_role] AS r LEFT JOIN [timers] AS t ON t.subject = r.user_id WHERE r.role = "agent" AND r.chat_id = ? AND t.current_event IS NULL', [$this->ticket->chat_id]);
        }
        
        while ($agent->next()) {
            $view = new BotView(Bot::$api, $agent->id);
            $view->show('privateUrgentNotification', null, ['ticket' => $this->ticket]);
        }
    }
    
    protected function notifyStatusChanging() {

        if (array_search('status', $this->event->fields) === false) {
            return;
        }
        
        switch ($this->ticket->status) {
            case ticket::STATUS_CLOSED:
                $this->notifyClosing();
                break;
            case ticket::STATUS_REOPEN:
                $this->notifyReopen();
                break;
            case ticket::STATUS_AWAITING_USER:
                $this->notifyAwaitingUser();
                break;
            case ticket::STATUS_USER_ANSWERED:
                $this->editTopic();
                break;
            case ticket::STATUS_ARCHIVED:
                $this->notifyArchived();
                break;
            case ticket::STATUS_IN_PROGRESS:
                $this->editTopic();
                break;
        }
    }
    
    protected function notifyAwaitingUser() {

        $params = ['mentions' => mentionByIdArray($this->ticket->getCustomers())];
        sendMessage(__('%mentions%, работа над заявкой приостановлена до получения ответа.', $params), null, $this->ticket->chat_id, $this->ticket->topic_id);

        $this->editTopic();
    }
    
    protected function notifyClosing() {
        $chat = chat::getById($this->ticket->chat_id);
        
        $view = new BotView(Bot::$api, $this->ticket->chat_id, $chat->language_code);
        $view->show('viewTicketClosing', 'kbdTicketClosing', [
            'mentions' => Service::mentionByIdArray($this->ticket->getCustomers()),
            'ticket_time_elapsed' => $this->ticket->getTimeElapsed()
            ], null, $this->ticket->topic_id);
        
        try {
            Bot::$api->closeForumTopic($this->ticket->chat_id, $this->ticket->topic_id);
        } catch (\Exception $ex) {
            //Bot::logException($ex);
        }
        
        if ($this->ticket->chat_id == Bot::param('chat_for_private_tickets', null)) {
            $this->notifyPrivateClosing();
        }
        
        $this->editTopic();
    }
    
    protected function notifyPrivateClosing() {
        $private_topic = new private_topic(['ticket_id' => $this->ticket->id], true);
        if ($private_topic->isNew()) {
            return;
        }
        sendMessage(__('Оператор закрыл ваше обращение.'), null, $private_topic->user_id);
    }
    
    protected function notifyPrivateReopen() {
        $private_topic = new private_topic(['ticket_id' => $this->ticket->id], true);
        if ($private_topic->isNew()) {
            return;
        }
        sendMessage(__('Ожидайте ответа оператора.'), null, $private_topic->user_id);
    }
    
    protected function notifyReopen() {
        
        $chat = chat::getById($this->ticket->chat_id);
        
        $view = new BotView(Bot::$api, $this->ticket->chat_id, $chat->language_code);
        $view->show('viewTicketReopen', 'kbdTicketReopen', [], null, $this->ticket->topic_id);

        try {
            Bot::$api->reopenForumTopic($this->ticket->chat_id, $this->ticket->topic_id);
        } catch (Exception $ex) {
            //Bot::logException($ex);
        }

        if ($this->ticket->chat_id == Bot::param('chat_for_private_tickets', null)) {
            $this->notifyPrivateReopen();
        }
        
        $this->editTopic();
    }

    protected function notifyArchived() {
    
        sendMessage(__('Эта заявка перенеcена в архив.'), null, $this->ticket->chat_id, $this->ticket->topic_id);
        $this->editTopic();
    }

    protected function notifyPriorityChange() {
        
        if (array_search('is_urgent', $this->event->fields) === false) {
            return;
        }
        
        if ($this->ticket->is_urgent) {
            Service::message(
                    'info', 
                    sprintf(Service::__('%s поднял приоритет заявки.'), Service::mentionById(Bot::$user->id)), 
                    null, 
                    $this->ticket->topic_id);
        } else {
            Service::message(
                    'info', 
                    sprintf(Service::__('%s понизил приоритет заявки.'), Service::mentionById(Bot::$user->id)), 
                    null, 
                    $this->ticket->topic_id);
        }
        
        $this->editTopic();
    }
    
    protected function notifyTypeChange() {

        if (array_search('is_task', $this->event->fields) === false) {
            return;
        }
        
        if (!$this->ticket->is_task) {
            Service::message(
                    'info', 
                    sprintf(Service::__('%s изменил тип заявки на Cообщение о неисправности.'), Service::mentionById(Bot::$user->id)), 
                    null, 
                    $this->ticket->topic_id);
        } else {
            Service::message(
                    'info', 
                    sprintf(Service::__('%s изменил тип заявки на Задача.'), Service::mentionById(Bot::$user->id)), 
                    null, 
                    $this->ticket->topic_id);
        }
        
        $this->editTopic();
    }
    
    protected function editTopic() {

        if ($this->ticket->status == ticket::STATUS_CLOSED) {
            $icon = Emoji::ID_FINISH;
        } elseif ($this->ticket->status == ticket::STATUS_ARCHIVED) {
            $icon = Emoji::ID_ARCHIVE;
        } elseif ($this->ticket->status == ticket::STATUS_AWAITING_USER) {
            $icon = Emoji::ID_QUESTION;
        } elseif ($this->ticket->is_urgent && !$this->ticket->is_task) {
            $icon = Emoji::ID_URGENT;
        } elseif (!$this->ticket->is_urgent && !$this->ticket->is_task) {
            $icon = Emoji::ID_EXCLAMATION;
        } elseif ($this->ticket->is_urgent && $this->ticket->is_task) {
            $icon = Emoji::ID_STAR;
        } else {
            $icon = Emoji::ID_NONE;
        }
        
        try {
            Bot::$api->editForumTopic($this->ticket->chat_id, $this->ticket->topic_id, $this->ticket->topic_title, $icon);
        } catch (\Exception $ex) {
            //Bot::logException($ex);
        }
        
    }
}
