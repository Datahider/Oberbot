<?php

namespace losthost\Oberbot\controller\command;

use losthost\DB\DB;
use losthost\Oberbot\data\user_chat_role;
use losthost\telle\Bot;

use function \losthost\Oberbot\getMentionedIds;
use function \losthost\Oberbot\message;
use function \losthost\Oberbot\mentionById;

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
                    $modified_mentions[] = mentionById($id);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Bot::logException($e);
        }
        
        if (count($modified_mentions)) {
            message('done', "Следующим пользователям присвоена роль агентов в этом чате:\n\n🔸 ". implode("\n🔸 ", $modified_mentions));
        } else {
            message('warning', "Ни одному из указанных пользователей не присвоена роль агента в этом чате.");
        }
        return true;
        
    }
}
