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
    
    protected array $mentioned_ids;


    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $this->prepareData($message);
        
        if (empty($this->mentioned_ids)) {
            $this->showHelp();
        } else {
            
            $unpaid = $this->getUnpaid();
            if (count($unpaid) > Bot::param('max_unpaid_per_group', 1)) {
                $this->reportUnpaid($unpaid);
            } else {
                $modified_mentions = $this->addAgents();
                $this->reportSuccess($modified_mentions);
            }

        }
            
        return true;
        
    }
    
    protected function prepareData(\TelegramBot\Api\Types\Message &$message) : void {
    
        $this->mentioned_ids = getMentionedIds($message);
    }
    
    protected function addAgents() : array {
        $modified_mentions = [];
        
        DB::beginTransaction();
        try {
            foreach ($this->mentioned_ids as $id) {
                $role = new user_chat_role(['user_id' => $id, 'chat_id' => $this->chat_id], true);
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
            throw $e;
        }
        
        return $modified_mentions;
        
    }
    
    protected function reportUnpaid(array $unpaid) {
        
    }
    
    protected function reportSuccess(array $modified) {
        
    }
    
    protected function getUnpaid() {
        
    }
}
