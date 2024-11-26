<?php

namespace losthost\Oberbot\data;

use losthost\timetracker\Timer;
use losthost\timetracker\TimerEvent;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\data\topic_user;
use losthost\DB\DBView;
use losthost\DB\DBValue;
use losthost\Oberbot\data\accepting_message;
use losthost\Oberbot\service\Service;
use losthost\DB\DB;
use losthost\telle\Bot;
use losthost\Oberbot\data\wait;

class ticket extends topic {
    
    const STATUS_CREATING = parent::STATUS_PENDING;
    const STATUS_NEW = parent::STATUS_NEW;
    const STATUS_REOPEN = 102;
    const STATUS_IN_PROGRESS = parent::STATUS_IN_PROGRESS;
    const STATUS_CLOSED = parent::STATUS_CLOSED;
    const STATUS_ARCHIVED = 120;
    
    const TYPE_TICKET = false;
    const TYPE_TASK = true;
    
    protected ?bool $was_archived = null;


    static public function create(int $group_id, int $thread_id, string $title, int $creator_id) {
        
        $ticket = new ticket(['topic_title' => $title, 'chat_id' => $group_id, 'topic_id' => $thread_id], true);
        if (!$ticket->isNew()) {
            throw new \Exception("Ticket already exists.");
        }
        $ticket->status = static::STATUS_CREATING;
        $ticket->last_activity = \time();
        $ticket->last_admin_activity = 0;
        $ticket->is_urgent = false;
        $ticket->is_task = true;
        $ticket->ticket_creator = $creator_id;
        
        $ticket->write('', ['function' => 'create']);
        return $ticket;
        
    }
    
    static public function getById(int $id) : ticket {
        $ticket = new ticket(['id' => $id]);
        return $ticket;
    }
    
    static public function getByGroupThread(int $group_id, ?int $thread_id) : ticket {
        $ticket = new ticket(['chat_id' => $group_id, 'topic_id' => $thread_id]);
        return $ticket;
    }

    public function accept() : ticket {
    
        if ($this->status != static::STATUS_CREATING) {
            throw new \Exception("Current ticket status is not CREATING");
        }
        
        $this->status = static::STATUS_NEW;
        $this->write('', ['function' => 'accept']);
        
        return $this;
        
    }
    
    public function touchUser() : ticket {
        $this->last_activity = \time();
        $this->write('', ['function' => 'touchUser']);
        return $this;
    }

    public function touchAdmin(int $user_id) : ticket {
        $this->last_admin_activity = \time();
        
        $topic_admin = new topic_admin(['topic_number' => $this->id, 'user_id' => $user_id], true);
        $topic_admin->last_activity = date_create();
        
        DB::beginTransaction();
        try {
            $topic_admin->isNew() || $topic_admin->write(); // может быть новый, если агент отвязался
                                                            // TODO -- проверить что делаем при касании не привязанного агента
                                                            // возможно надо сделать touchCustomer
            $this->write('', ['function' => 'touchAdmin']);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Bot::logException($ex);
        }
        return $this;
    }

    public function toTask() : ticket {
        $this->is_task = true;
        $this->write('', ['function' => 'toTask']);
        return $this;
    }
    
    public function toTicket() : ticket {
        $this->is_task = false;
        $this->write('', ['function' => 'toTask']);
        return $this;
    }

    public function setUrgent(bool $urgent = true) : ticket {
        $this->is_urgent = $urgent;
        if ($urgent) {
            $this->write('', ['function' => 'setUrgent']);
        } else {
            $this->write('', ['function' => 'resetUrgent']);
        }
        return $this;
    }
    
    public function resetUrgent() : ticket {
        return $this->setUrgent(false);
    }
    
    public function close() : ticket {
        
        $this->timerStop();
        $this->status = static::STATUS_CLOSED;
        $this->write('', ['function' => 'close']);
        
        return $this;
    }

    public function reopen() : ticket {
        $this->status = static::STATUS_REOPEN;
        $this->write('', ['function' => 'reopen']);
        
        return $this;
    }

    public function rate(int|string $score) {
        switch ($score) {
            case -1:
            case 'bad':
                $this->score = -1;
                break;
            case 0:
            case 'acceptable':
                $this->score = 0;
                break;
            case 1:
            case 'good':
                $this->score = 1;
                break;
        }
        
        $this->isModified() && $this->write('', ['function' => 'rate']);
        return $this;
    }
    
    public function archive() : ticket {
        if ($this->status != static::STATUS_CLOSED) {
            throw new \Exception("Can not archive non-closed ticket.");
        }
        $this->status = static::STATUS_ARCHIVED;
        $this->write('', ['function' => 'archive']);
        
        return $this;
    }
    
    public function timerStart(int $user_id) : ticket {
        
        if ($this->isTimerStarted($user_id)) { // Ничего не делаем если уже запущен для этого тикета
            return $this;
        }
        
        $statuses_allowed = [
            static::STATUS_NEW,
            static::STATUS_IN_PROGRESS,
            static::STATUS_REOPEN,
        ];
        
        if (array_search($this->status, $statuses_allowed) === false) {
            throw new \Exception("Current ticket status does not allow to start timer.");
        }
        
        $this->status = static::STATUS_IN_PROGRESS;
        $this->isModified() && $this->write('', ['function' => __FUNCTION__]);
        
        $timer = new Timer($user_id);
        if ($timer->isStarted()) {
            $timer->stop();
        }
        $timer->start($this->id, $this->chat_id);
        
        return $this;
    }
    
    public function timerStop(int|string $user_id='all') : ticket {
        if ($user_id == 'all') {
            $timers = Timer::getStartedByObjectProject($this->id, $this->chat_id);
        } else {
            $timers[] = new Timer($user_id);
        }
        
        foreach ($timers as $timer) {
            $timer->stop($user_id);
        }
        
        return $this;
    }
    
    public function isTimerStarted(int $user_id) : bool {
        $timer = new Timer($user_id);
        if ($timer->isStarted()) {
            $timer_event = new TimerEvent($timer, $timer->current_event);
            if ($timer_event->object == $this->id) {
                return true;
            }
            return false;
        }
        
        return false;
    }

    public function getTimeElapsed() {
        $seconds_elapsed = new DBValue(<<<FIN
            SELECT 
                SUM(TIMESTAMPDIFF(SECOND, e.start_time, e.end_time)) AS value
            FROM 
                [timer_events] AS e
            WHERE
                e.object = ?
                AND e.project = ?
                AND e.started = 0
            FIN, [$this->id, $this->chat_id]
        );
        
        return Service::seconds2dateinterval($seconds_elapsed->value);
    }
    
    public function linkCustomer(int $user_id) {
        if ($this->hasAgent($user_id)) {
            throw new \Exception("Can't link ticket's agent as a customer");
        }
        $customer_link = new topic_user(['topic_number' => $this->id, 'user_id' => $user_id], true);
        if (!$customer_link->isNew()) {
            throw new \Exception('Customer is already linked.');
        }
        $customer_link->write();
        return $this;
    }
    
    public function linkAgent(int $user_id) {
        $this->unlinkCustomer($user_id, true);
        $agent_link = new topic_admin(['topic_number' => $this->id, 'user_id' => $user_id], true);
        $agent_link->isNew() && $agent_link->write();
        return $this;
    }
    
    public function unlinkCustomer(int $user_id, bool $mute=false) {
        $customer_link = new topic_user(['topic_number' => $this->id, 'user_id' => $user_id], true);
        $customer_link->isNew() || $customer_link->delete('', ['mute' => $mute]);
        return $this;
    }
    
    public function unlinkAgent(int $user_id) {
        $agent_link = new topic_admin(['topic_number' => $this->id, 'user_id' => $user_id], true);
        $agent_link->isNew() || $agent_link->delete();
        return $this;
    }
    
    public function unlink(int $user_id) {
        $this->unlinkCustomer($user_id);
        $this->unlinkAgent($user_id);
        return $this;
    }
    
    public function hasCustomer(int $user_id) {
        $customer_link = new topic_user(['topic_number' => $this->id, 'user_id' => $user_id], true);
        return !$customer_link->isNew();
    }
    
    public function hasAgent(int $user_id) {
        $agent_link = new topic_admin(['topic_number' => $this->id, 'user_id' => $user_id], true);
        return !$agent_link->isNew();
    }
    
    public function getCustomers() {
        $customer_ids = new DBView('SELECT user_id FROM [topic_users] WHERE topic_number = ?', [$this->id]);
        $result = [];
        
        while ($customer_ids->next()) {
            $result[] = $customer_ids->user_id;
        }
        return $result;
    }

    public function getAgents() {
        $agent_ids = new DBView('SELECT user_id FROM [topic_admins] WHERE topic_number = ?', [$this->id]);
        $result = [];
        
        while ($agent_ids->next()) {
            $result[] = $agent_ids->user_id;
        }
        return $result;
    }
    
    public function getAcceptedMessageId() {
        $accepted_message = new accepting_message(['ticket_id' => $this->id], true);
        if ($accepted_message->isNew()) {
            return null;
        }
        return $accepted_message->message_id;
    }
    
    public function setAcceptedMessageId(int $message_id) {
        $accepted_message = new accepting_message(['ticket_id' => $this->id], true);
        $accepted_message->message_id = $message_id;
        $accepted_message->isModified() && $accepted_message->write();
    }
    
    public function waitTime(\DateTime|\DateTimeImmutable $time) {
        $this->wait_till = $time->format(DB::DATE_FORMAT);
        $this->write();
    }
    
    public function waitTask(int $ticket_id) {
        $wait = new wait(['task_id' => $this->id, 'subtask_id' => $ticket_id], true);
        if ($wait->isNew()) {
            $wait->write();
            return true;
        }
        return false;
    }
    
    protected function entityTaskName($case) {
        switch ($case) {
            case 1:
                return "задача";
            case 2:
                return "задачи";
            case 3:
                return "задаче";
            case 4:
                return "задачу";
            case 5:
                return "задачей";
            case 6:
                return "задаче";
            default:
                throw new Exception('Указан не верный падеж.');
        }
    }
    
    protected function entityTicketName($case) {
        switch ($case) {
            case 1:
                return "заявка";
            case 2:
                return "заявки";
            case 3:
                return "заявке";
            case 4:
                return "заявку";
            case 5:
                return "заявкой";
            case 6:
                return "заявке";
            default:
                throw new Exception('Указан не верный падеж.');
        }
    }
    
    public function entityName($case=1, $capitalize=false) {

        if ($this->is_task) {
            $result = $this->entityTaskName($case);
        } else {
            $result = $this->entityTicketName($case);
        }
        
        if ($capitalize) {
            $result = mb_strtoupper(mb_substr($result, 0, 1)). mb_substr($result, 1);
        }
        
        return $result;
    }
    
    protected function beforeModify($name, $value) {
        if ($name === 'status' && $this->was_archived === null) {
            $this->was_archived = $this->status == static::STATUS_ARCHIVED;
        }
        parent::beforeModify($name, $value);
    }
    protected function beforeUpdate($comment, $data) {
        if ($this->was_archived === true && $this->status != self::STATUS_ARCHIVED) {
            throw new \Exception("Can not change ticket status as it was archived.");
        }
        parent::beforeUpdate($comment, $data);
    }
    
    public function __get($name) {
        if ( $name == 'title' ) {
            $name = 'topic_title';
        }
        return parent::__get($name);
    }
    
    public function __set($name, $value) {
        if ($name == 'title') {
            $name = 'topic_title';
        }
        parent::__set($name, $value);
    }
}
