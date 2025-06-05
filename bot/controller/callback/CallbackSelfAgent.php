<?php

namespace losthost\Oberbot\controller\callback;

use losthost\DB\DB;
use losthost\Oberbot\data\user_chat_role;
use losthost\DB\DBView;
use losthost\telle\Bot;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionByView;
use function \losthost\Oberbot\sendMessage;

class CallbackSelfAgent extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^self_agent$/";
    const PERMIT = self::PERMIT_ADMIN;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $group_id = $callback_query->getMessage()->getChat()->getId();
        $user_id = $callback_query->getFrom()->getId();
        $message_id = $callback_query->getMessage()->getMessageId();

        DB::beginTransaction();
        try {
            $role = new user_chat_role(['user_id' => $user_id, 'chat_id' => $group_id], true);
            if ($role->role != user_chat_role::ROLE_AGENT) {
                $role->role = user_chat_role::ROLE_AGENT;
                $role->write();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Bot::logException($e);
            throw $e;
        }
        
        $agent_ids = new DBView('SELECT user_id FROM [user_chat_role] WHERE role = "agent" AND chat_id = ?', [Bot::$chat->id]);
        $all_agents = mentionByView($agent_ids, '-', true, 'user_id');

        // Bot::$api->editMessageReplyMarkup($group_id, $message_id);
        sendMessage(__("Актуальный список агентов: %all_agents%", compact('all_agents')));
        return true;
        
        
    }
}
