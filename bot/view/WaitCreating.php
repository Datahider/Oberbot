<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\Oberbot\data\wait;
use losthost\Oberbot\data\ticket;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\ticketMentionNoId;

class WaitCreating extends DBTracker {
    
    protected wait $wait;
    protected ticket $task;
    protected ticket $subtask;


    public function track(\losthost\DB\DBEvent $event) {
        
        $this->wait = $event->object;
        $this->task = ticket::getById($this->wait->task_id);
        $this->subtask = ticket::getById($this->wait->subtask_id);
        
        sendMessage(__("Эта %entity% отложена до решения %subtask%", ['entity' => $this->task->entityName(), 'subtask' => ticketMentionNoId($this->subtask)]), null, $this->task->chat_id, $this->task->topic_id);
    }
}
