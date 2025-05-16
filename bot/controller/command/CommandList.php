<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\Oberbot\data\chat_group;
use losthost\Oberbot\service\Service;
use losthost\DB\DBView;
use losthost\telle\Bot;
use losthost\Oberbot\data\session;
use losthost\Oberbot\controller\display\ActiveListDisplay;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class CommandList extends AbstractAuthCommand {
    
    const COMMAND = 'list';
    const DESCRIPTION = [
        'default' => 'Выбор активного списка',
        'all_group_chats' => 'Включение группы из список(ки)'
    ];

    const PERMIT = self::PERMIT_AGENT | self::PERMIT_PRIVATE;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        if ($message->getChat()->getType() == 'private') {
            $this->processPrivate($message);
            return true;
            // TODO - то что после if вынести в отдельный метод
        }
        if ($message->getMessageThreadId() > 1) {
            Service::message('warning', 'Эта команда предназначена для использования только в общем чате группы.');
            return true;
        }
        
        $chat_id = $message->getChat()->getId();
        $msg_type = 'info';
        
        if ($this->args) {
            $group_array = preg_split("/\s*\,\s*/", $this->args);
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
            Service::message($msg_type, implode(", ", $groups));
        } else {
            Service::message($msg_type, 'Группы не заданы.');
        }
        
        return true;
    }
    
    protected function processPrivate(\TelegramBot\Api\Types\Message &$message) {
        
        ActiveListDisplay::display(Bot::$user->id, Bot::$chat->id);
    }
    
    static protected function permit(): int {
        return self::PERMIT;
    }

    static public function description(): array {
        return self::DESCRIPTION;
    }
    
}
