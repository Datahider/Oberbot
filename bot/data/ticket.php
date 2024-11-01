<?php

namespace losthost\Oberbot\data;

use losthost\timetracker\Timer;
use losthost\DB\DBView;
use losthost\Oberbot\data\topic;

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


    static public function create(int $group_id, int $thread_id, string $title) {
        
        $ticket = new ticket(['topic_title' => $title, 'chat_id' => $group_id, 'topic_id' => $thread_id], true);
        if (!$ticket->isNew()) {
            throw new \Exception("Ticket already exists.");
        }
        $ticket->status = static::STATUS_CREATING;
        $ticket->last_activity = time();
        $ticket->last_admin_activity = 0;
        $ticket->is_urgent = false;
        $ticket->is_task = false;
        
        $ticket->write('', ['function' => 'create']);
        return $ticket;
        
    }
    
    static public function accept(int $group_id, int $thread_id) {
    
        $ticket = new ticket(['topic_id' => $thread_id, 'chat_id' => $group_id]);
        if ($ticket->status != static::STATUS_CREATING) {
            throw new \Exception("Current ticket status is not CREATING");
        }
        
        $ticket->status = static::STATUS_NEW;
        $ticket->write('', ['function' => 'accept']);
        
        return $ticket;
        
    }
    
    static public function touch(int $group_id, int $thread_id, bool $admin_touch=false) {
        
        $ticket = new ticket(['topic_id' => $thread_id, 'chat_id' => $group_id]);

        if ($admin_touch) {
            $ticket->last_admin_activity = time();
        } else {
            $ticket->last_activity = time();
        }
        
        $ticket->write('', ['function' => 'touch']);
        
        return $ticket;
    }

    static public function type(int $group_id, int $thread_id, bool $ticket_type) {
    
        $ticket = new ticket(['topic_id' => $thread_id, 'chat_id' => $group_id]);
        $ticket->is_task = $ticket_type;
        $ticket->write('', ['function' => 'type']);
        
        return $ticket;
    }

    static public function urgent(int $group_id, int $thread_id, bool $urgent) {
    
        $ticket = new ticket(['topic_id' => $thread_id, 'chat_id' => $group_id]);
        $ticket->is_urgent = $urgent;
        $ticket->write('', ['function' => 'urgent']);
        
        return $ticket;
    }
    
    static public function close(int $group_id, int $thread_id) {
        
        $timers = Timer::getStartedByObjectProject($thread_id, $group_id);
        foreach ($timers as $timer) {
            $timer->stop();
        }
        
        $ticket = new ticket(['topic_id' => $thread_id, 'chat_id' => $group_id]);
        $ticket->status = static::STATUS_CLOSED;
        $ticket->write('', ['function' => 'close']);
        
        return $ticket;
    }

    static public function reopen(int $group_id, int $thread_id) {
        $ticket = new ticket(['topic_id' => $thread_id, 'chat_id' => $group_id]);
        $ticket->status = static::STATUS_REOPEN;
        $ticket->write('', ['function' => 'reopen']);
        
        return $ticket;
    }

    static public function archive(int $group_id, int $thread_id) {
        $ticket = new ticket(['topic_id' => $thread_id, 'chat_id' => $group_id]);
        if ($ticket->status != static::STATUS_CLOSED) {
            throw new \Exception("Can not archive non-closed ticket.");
        }
        $ticket->status = static::STATUS_ARCHIVED;
        $ticket->write('', ['function' => 'archive']);
        
        return $ticket;
    }
    
    static public function timerStart(int $group_id, int $thread_id, int $user_id) {
        $statuses_allowed = [
            static::STATUS_NEW,
            static::STATUS_IN_PROGRESS
        ];
        
        $ticket = new ticket(['topic_id' => $thread_id, 'chat_id' => $group_id]);
        if (array_search($ticket->status, $statuses_allowed) === false) {
            throw new \Exception("Current ticket status does not allow to start timer.");
        }
        
        $ticket->status = static::STATUS_IN_PROGRESS;
        $ticket->isModified() && $ticket->write('', ['function' => __FUNCTION__]);
        
        $timer = new Timer($user_id);
        $timer->start($thread_id, $group_id);
        
        return $ticket;
    }
    
    static public function timerStop(int $group_id, int $thread_id, int|string $user_id='all') {
        $ticket = new ticket(['topic_id' => $thread_id, 'chat_id' => $group_id]);
        
        if ($user_id == 'all') {
            $timers = Timer::getStartedByObjectProject();
        } else {
            $timers[] = new Timer($user_id);
        }
        
        foreach ($timers as $timer) {
            $timer->stop();
        }
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
