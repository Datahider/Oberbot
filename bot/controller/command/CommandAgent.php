<?php

namespace losthost\Oberbot\controller\command;

use losthost\DB\DB;
use losthost\Oberbot\data\user_chat_role;
use losthost\telle\Bot;
use losthost\DB\DBView;

use function \losthost\Oberbot\getMentionedIds;
use function \losthost\Oberbot\mentionByView;
use function \losthost\Oberbot\mentionByIdArray;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class CommandAgent extends AbstractAuthCommand {
    
    const COMMAND = 'agent';
    const PERMIT = self::PERMIT_ADMIN;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $ids = getMentionedIds($message);
        $modified_mentions = [];
        
        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $role = new user_chat_role(['user_id' => $id, 'chat_id' => $message->getChat()->getId()], true);
                if ($role->role != user_chat_role::ROLE_AGENT) {
                    $role->role = user_chat_role::ROLE_AGENT;
                    $role->write();
                    $modified_mentions[] = $id;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Bot::logException($e);
        }
        
        $agent_ids = new DBView('SELECT user_id FROM [user_chat_role] WHERE role = "agent" AND chat_id = ?', [$message->getChat()->getId()]);
        $all_agents = mentionByView($agent_ids, '-', true, 'user_id');
        $added_agents = mentionByIdArray($modified_mentions, '-', true);

        sendMessage(__("Актуальный список агентов: %all_agents%\n\nДобавлены: <b>%added_agents%</b>", compact('all_agents', 'added_agents')));
        return true;
        
    }
}
