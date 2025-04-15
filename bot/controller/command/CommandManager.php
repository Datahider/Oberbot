<?php

namespace losthost\Oberbot\controller\command;

use losthost\DB\DB;
use losthost\Oberbot\data\user_chat_role;
use losthost\telle\Bot;
use losthost\DB\DBView;
use TelegramBot\Api\Types\BotCommand;

use function \losthost\Oberbot\getMentionedIds;
use function \losthost\Oberbot\mentionByView;
use function \losthost\Oberbot\mentionByIdArray;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class CommandManager extends AbstractAuthCommand {
    
    const COMMAND = 'manager';
    const DESCRIPTION = [
        'default' => 'Назначение менеджера в группе (который может согласовывать приоритетность задач)',
    ];
    const PERMIT = self::PERMIT_ADMIN;
    
    protected array $mentioned_ids;


    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $this->prepareData($message);
        
        $modified_mentions = $this->addManagers();
        $this->reportSuccess($modified_mentions);
            
        return true;
        
    }
    
    protected function prepareData(\TelegramBot\Api\Types\Message &$message) : void {
    
        $this->mentioned_ids = getMentionedIds($message);
    }
    
    protected function addManagers() : array {
        $modified_mentions = [];
        
        DB::beginTransaction();
        try {
            foreach ($this->mentioned_ids as $id) {
                $role = new user_chat_role(['user_id' => $id, 'chat_id' => $this->chat_id], true);
                if ($role->role != user_chat_role::ROLE_MANAGER) {
                    $role->role = user_chat_role::ROLE_MANAGER;
                    $role->write();
                    $modified_mentions[] = $id;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Bot::logException($e);
            throw $e;
        }
        
        return $modified_mentions;
        
    }
    
    protected function reportSuccess(array $modified) {
        $manager_ids = new DBView('SELECT user_id FROM [user_chat_role] WHERE role = "manager" AND chat_id = ?', [Bot::$chat->id]);
        $all_managers = mentionByView($manager_ids, '-', true, 'user_id');
        $added_managers = mentionByIdArray($modified, '-', true);

        sendMessage(__("Актуальный список менеджеров: %all_managers%\n\nДобавлены: <b>%added_managers%</b>", compact('all_managers', 'added_managers')));
        return true;
    }
    
}
