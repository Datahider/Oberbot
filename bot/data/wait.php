<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;

class wait extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'task_id' => 'BIGINT(20) NOT NULL',
        'subtask_id' => 'BIGINT(20) NOT NULL',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX task_subtask' => ['task_id', 'subtask_id']
    ];
    
    protected function beforeInsert($comment, $data) {
        $this->checkDeadlock();
        parent::beforeInsert($comment, $data);
    }
    
    protected function beforeUpdate($comment, $data) {
        $this->checkDeadlock();
        parent::beforeUpdate($comment, $data);
    }
    
    protected function checkDeadlock() {
        $backward_connection = new wait(['task_id' => $this->subtask_id, 'subtask_id' => $this->task_id], true);
        if (!$backward_connection->isNew()) {
            throw new \Exception('Tasks cannot be subtasks of each other.');
        }
    }
}
