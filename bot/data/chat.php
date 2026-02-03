<?php

namespace losthost\Oberbot\data;

use losthost\DB\DBObject;
use losthost\DB\DBView;
use losthost\Oberbot\data\user_chat_role;
use losthost\telle\Bot;

class chat extends DBObject {
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL',
        'process_tickets' => 'TINYINT(1) NOT NULL',
        'language_code' => 'VARCHAR(8)',
        'delete_commands' => 'TINYINT(1)',
        'wizard_message_id' => 'BIGINT(20)',
        'chat_settings_id' => 'BIGINT(20)',
        'nice' => 'DECIMAL(5,2) NOT NULL DEFAULT 0',
        'PRIMARY KEY' => 'id'
    ];
    
    static public function getById(int $id, ?string $language_code=null) {
        $chat = new chat(['id' => $id], true);
        if ($chat->isNew()) {
            if (isset($language_code)) {
                $chat->language_code = $language_code;
            }
            $chat->process_tickets = true;
            $chat->write();
        }
        return $chat;
    }
    
    public function write($comment = '', $data = null) {
        if ($this->nice === null) {
            $this->nice = 0;
        }
        parent::write($comment, $data);
    }

    public function getCustomerIds() : array {
        
        $agent_ids_as_string = implode(', ', $this->getAgentIds());
        $bot_userid = Bot::param('bot_userid', -1);
        $ids = new DBView("SELECT user_id FROM [chat_user] WHERE user_id NOT IN ($bot_userid, $agent_ids_as_string) AND chat_id = ?", [$this->id]);
        
        $result = [];
        while ($ids->next()) {
            $result[] = $ids->user_id;
        }
        
        return $result;
    }
    
    public function isAgent(int $user_id) : bool {
        
        $role = new user_chat_role(['user_id' => $user_id, 'chat_id' => $this->id], true);
        if ($role->isNew()) {
            return false;
        }
        return $role->role == 'agent';
    }
    
    public function isManager(int $user_id) : bool {
        
        $role = new user_chat_role(['user_id' => $user_id, 'chat_id' => $this->id], true);
        if ($role->isNew()) {
            return false;
        }
        return $role->role == 'manager';
    }
    
    public function getAgentIds() : array {
        
        $ids = new DBView('SELECT user_id FROM [user_chat_role] WHERE role = "agent" AND chat_id = ?', [$this->id]);
        
        $result = [];
        while ($ids->next()) {
            $result[] = $ids->user_id;
        }
        
        return $result;
    }
    
    public function getManagerIds() : array {
        
        $ids = new DBView('SELECT user_id FROM [user_chat_role] WHERE role = "manager" AND chat_id = ?', [$this->id]);
        
        $result = [];
        while ($ids->next()) {
            $result[] = $ids->user_id;
        }
        
        return $result;
    }
}

