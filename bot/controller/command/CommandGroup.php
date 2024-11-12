<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\Oberbot\data\chat_group;
use losthost\Oberbot\service\Service;
use losthost\DB\DBView;

class CommandGroup extends AbstractAuthCommand {
    
    const COMMAND = 'group';
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        if ($message->getMessageThreadId() > 1) {
            Service::message('warning', 'Эта команда предназначена для использования только в общем чате группы.');
            return true;
        }
        
        $chat_id = $message->getChat()->getId();
        $msg_type = 'info';
        
        if ($this->args) {
            $group_array = explode(" ", $this->args);
            foreach ($group_array as $group) {
                $chat_group = new chat_group(['chat_id' => $chat_id, 'chat_group' => $group], true);
                $chat_group->isNew() && $chat_group->write();
            }
            $msg_type = 'done';
        }
        
        $group = new DBView('SELECT * FROM [chat_groups] WHERE chat_id = ?', [$chat_id]);
        
        $groups = [];
        while ($group->next()) {
            $groups[] = $group->chat_group;
        }
        
        if (count($groups)) {
            Service::message($msg_type, implode(" ", $groups));
        } else {
            Service::message($msg_type, 'Группы не заданы.');
        }
        
        return true;
    }
}
