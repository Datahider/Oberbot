<?php

namespace losthost\Oberbot\data;

use losthost\timetracker\Timer;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\data\topic_user;

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
        $ticket->is_task = false;
        $ticket->ticket_creator = $creator_id;
        
        $ticket->write('', ['function' => 'create']);
        return $ticket;
        
    }
    
    static public function getById(int $id) : ticket {
        $ticket = new ticket(['id' => $id]);
        return $ticket;
    }
    
    static public function getByGroupThread(int $group_id, int $thread_id) : ticket {
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

    public function touchAdmin() : ticket {
        $this->last_admin_activity = \time();
        $this->write('', ['function' => 'touchAdmin']);
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
        
        $timers = Timer::getStartedByObjectProject($this->topic_id, $this->chat_id);
        foreach ($timers as $timer) {
            $timer->stop();
        }
        
        $this->status = static::STATUS_CLOSED;
        $this->write('', ['function' => 'close']);
        
        return $this;
    }

    public function reopen() : ticket {
        $this->status = static::STATUS_REOPEN;
        $this->write('', ['function' => 'reopen']);
        
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
        $statuses_allowed = [
            static::STATUS_NEW,
            static::STATUS_IN_PROGRESS
        ];
        
        if (array_search($this->status, $statuses_allowed) === false) {
            throw new \Exception("Current ticket status does not allow to start timer.");
        }
        
        $this->status = static::STATUS_IN_PROGRESS;
        $this->isModified() && $this->write('', ['function' => __FUNCTION__]);
        
        $timer = new Timer($user_id);
        $timer->start($this->topic_id, $this->chat_id);
        
        return $this;
    }
    
    public function timerStop(int|string $user_id='all') : ticket {
        if ($user_id == 'all') {
            $timers = Timer::getStartedByObjectProject($this->topic_id, $this->chat_id);
        } else {
            $timers[] = new Timer($user_id);
        }
        
        foreach ($timers as $timer) {
            $timer->stop();
        }
        
        return $this;
    }

    public function linkCustomer(int $user_id) {
        $customer_link = new topic_user(['topic_number' => $this->id, 'user_id' => $user_id], true);
        $customer_link->isNew() && $customer_link->write();
        return $this;
    }
    
    public function linkAgent(int $user_id) {
        $agent_link = new topic_admin(['topic_number' => $this->id, 'user_id' => $user_id], true);
        $agent_link->isNew() && $agent_link->write();
        return $this;
    }
    
    public function unlinkCustomer(int $user_id) {
        $customer_link = new topic_user(['topic_number' => $this->id, 'user_id' => $user_id], true);
        $customer_link->isNew() || $customer_link->delete();
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
