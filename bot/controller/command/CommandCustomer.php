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

class CommandCustomer extends AbstractAuthCommand {
    
    const COMMAND = 'customer';
    const DESCRIPTION = [
        'default' => '"Разжалование" агентов в группе',
        'all_chat_administrators' => '"Разжалование" агентов в группе',
    ];
    const PERMIT = self::PERMIT_ADMIN;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $ids = getMentionedIds($message);
        $modified_mentions = [];
        
        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $role = new user_chat_role(['user_id' => $id, 'chat_id' => $message->getChat()->getId()], true);
                if (!$role->isNew()) {
                    $role->delete();
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
        $deleted_agents = mentionByIdArray($modified_mentions, '-', true);

        sendMessage(__("Актуальный список агентов: %all_agents%\n\nУдалены: <b>%deleted_agents%</b>", compact('all_agents', 'deleted_agents')));
        return true;
        
    }
    
    static protected function permit(): int {
        return self::PERMIT;
    }

    static public function description(): array {
        return self::DESCRIPTION;
    }
    
}
