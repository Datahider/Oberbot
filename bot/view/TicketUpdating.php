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
use function \losthost\Oberbot\mentionById;

class TicketUpdating extends DBTracker {
    
    protected DBEvent $event;
    protected ticket $ticket;
    
    public function track(\losthost\DB\DBEvent $event) {
        
        $this->ticket = $event->object;
        $this->event = $event;
        
        $this->destroyIncompleteTimer();
        $this->notifyTypeChange();
        $this->notifyStatusChanging();
        $this->notifyUrgentTicket();
        $this->notifyWaitTill();

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
        if (array_search('type', $this->event->fields) === false 
                && array_search('status', $this->event->fields) === false) {
            return;
        }
        
        $skip_statuses = [
            ticket::STATUS_CLOSED,
            ticket::STATUS_ARCHIVED,
            ticket::STATUS_IN_PROGRESS,
            ticket::STATUS_AWAITING_USER
        ];
        
        if ( array_search($this->ticket->status,  $skip_statuses) !== false ) {
            return;
        }
        if ($this->ticket->type == ticket::TYPE_REGULAR_TASK) {
            return;
        }
        
        // TODO -- доставать так же language_code
        if ($this->ticket->type == ticket::TYPE_PRIORITY_TASK) {
            // О срочной задаче уведомляем свободных
            $agent = new DBView('SELECT user_id AS id FROM [user_chat_role] AS r LEFT JOIN [timers] AS t ON t.subject = r.user_id WHERE r.role = "agent" AND r.chat_id = ? AND t.current_event IS NULL', [$this->ticket->chat_id]);
            $agent_ids = $this->usersToArray($agent);
        } else {
            $agent_ids = $this->ticket->getAgents();
            if (count($agent_ids) == 0) { 
                $agent = new DBView('SELECT user_id AS id FROM [user_chat_role] WHERE role = "agent" AND chat_id = ?', [$this->ticket->chat_id]);
                $agent_ids = $this->usersToArray($agent);
            }
        }
        
        foreach ($agent_ids as $id) {
            $view = new BotView(Bot::$api, $id);
            $view->show('privateUrgentNotification', null, ['ticket' => $this->ticket]);
        }
    }

    protected function usersToArray(DBView $dbview) : array {
        $result = [];
        while($dbview->next()) {
            $result[] = $dbview->user_id;
        }
        return $result;
    }
    
    protected function notifyWaitTill() {
        if (array_search('wait_till', $this->event->fields) === false) {
            return;
        }
        
        sendMessage(__("%entity% отложена до %till%", [
            'entity' => $this->ticket->entityName(1, true),
            'till' => date_create($this->ticket->wait_till)->format('d-m-Y H:i')
        ]), null, $this->ticket->chat_id, $this->ticket->topic_id);
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
        try {
            sendMessage(__('Оператор закрыл ваше обращение.'), null, $private_topic->user_id);
        } catch (\Exception $ex) {
            Bot::logException($ex);
        }
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

    protected function notifyTypeChange() {

        if (array_search('type', $this->event->fields) === false) {
            return;
        }
        
        Service::message(
                'info', 
                __('%mention% изменил тип заявки на %new_type%.', ['mention' => mentionById(Bot::$user->id), 'new_type' => __('type_'. $this->ticket->type)]), 
                null, 
                $this->ticket->topic_id);

        $this->editTopic();
    }
    
    protected function editTopic() {

        if ($this->ticket->status == ticket::STATUS_CLOSED) {
            $icon = Emoji::ID_FINISH;
        } elseif ($this->ticket->status == ticket::STATUS_ARCHIVED) {
            $icon = Emoji::ID_ARCHIVE;
        } elseif ($this->ticket->status == ticket::STATUS_AWAITING_USER) {
            $icon = Emoji::ID_QUESTION;
        } else {
            $icon = Emoji::TOPIC_ICONS_BY_TYPE[$this->ticket->type];
        }
        
        try {
            Bot::$api->editForumTopic($this->ticket->chat_id, $this->ticket->topic_id, $this->ticket->topic_title, $icon);
        } catch (\Exception $ex) {
            //Bot::logException($ex);
        }
        
    }
}
