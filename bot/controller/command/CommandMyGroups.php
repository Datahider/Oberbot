<?php

namespace losthost\Oberbot\controller\command;

use losthost\DB\DBView;
use losthost\telle\Bot;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\groupMentionById;

class CommandMyGroups extends AbstractAuthCommand {
    
    const COMMAND = 'mygroups';
    
    const ROLE_OWNER = 0;
    const ROLE_ADMIN = 1;
    const ROLE_MANAGER = 2;
    const ROLE_AGENT = 3;
    const ROLE_USER = 4;
    const ROLE_RESTRICTED = 5;
    const ROLE_LEFT = 6;
    const ROLE_BANNED = 7;

    const ICONS = [
        '👑', //
        '🎩',
        '🧢',
        '🪖',
        '🎓',
        '🤐',
        '🤷‍♂️',
        '⛔️',
    ];
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $all_my_groups = $this->getAllMyGroups();
       
        $list = [];
        foreach ($all_my_groups as $my_group) {
            $group_line = ' - '. groupMentionById($my_group['chat_id']);
          
            if ($my_group['role'] == 'manager') {
                $group_line = static::ICONS[static::ROLE_MANAGER]. $group_line;
            } elseif ($my_group['role'] == 'agent') {
                $group_line = static::ICONS[static::ROLE_AGENT]. $group_line;
            } else {
                $group_line = static::ICONS[static::ROLE_USER]. $group_line;
            }
            
            try {
                $chat_member = Bot::$api->getChatMember($my_group['chat_id'], $this->user_id);
                $status = $chat_member->getStatus();
                switch ($status) {
                    case 'creator':
                        $group_line = static::ICONS[static::ROLE_OWNER]. $group_line;
                        break;
                    case 'administrator':
                        $group_line = static::ICONS[static::ROLE_ADMIN]. $group_line;
                        break;
                    case 'restricted':
                        $group_line = static::ICONS[static::ROLE_RESTRICTED]. $group_line;
                        break;
                    case 'left':
                        $group_line = static::ICONS[static::ROLE_LEFT]. $group_line;
                        break;
                    case 'kicked':
                        $group_line = static::ICONS[static::ROLE_BANNED]. $group_line;
                        break;
                }
            } catch (Exception $exc) {
                // Do nothing;
                #todo Если чувак не в чате, но мы тут, значит его надо исключить из chat_user
            }

            $list[] = $group_line;
        }
        
        if (empty($list)) {
            sendMessage(__("У нас свами нет общих групп."));
        } else {
            sendMessage(implode("\n", $list));
        }
        return true;
    }

    protected static function permit(): int {
        return self::PERMIT_PRIVATE;   
    }

    public static function description(): array {
        [
            'default' => 'Выводит список групп ролями в группе, обозначенными с помощью Emoji'
        ];
    }
    
    protected function getAllMyGroups() : array {
    
        $sql = <<<FIN
                SELECT 
                    cu.id, cu.chat_id, cu.last_seen, cr.role 
                FROM 
                    [chat_user] AS cu 
                    INNER JOIN [user_chat_role] AS cr ON cu.chat_id = cr.chat_id AND cu.user_id = cr.user_id
                WHERE 
                    cu.user_id = ?
                FIN;
        
        $view = new DBView($sql, $this->user_id);
        
        $result = [];
        while ($view->next()) {
            $result[] = [
                'id' => $view->id,
                'chat_id' => $view->chat_id,
                'last_seen' => $view->last_seen,
                'role' => $view->role,
            ];
        }
        
        return $result;
    
    }

}
