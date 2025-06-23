<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\DB\DBView;

class CallbackUrgent extends AbstractCallback {

    const CALLBACK_DATA_PATTERN = "/^(urgent)$/";
    const PERMIT = self::PERMIT_MANAGER;

    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {

        $ticket = ticket::getByGroupThread(
                $callback_query->getMessage()->getChat()->getId(), 
                $callback_query->getMessage()->getMessageThreadId());
        
        $this->setType($ticket);
        
        try {
            Bot::$api->editMessageReplyMarkup(Bot::$chat->id, $callback_query->getMessage()->getMessageId());
        } catch (Exception $ex) {
            Bot::logException($ex);
        }
        
        return true;
    }
    
    protected function setType(ticket $ticket) {
        
        $ticket->setType(ticket::TYPE_PRIORITY_TASK);
        
        # ticket::STATUS_ARCHIVED --------------------------------------------------------------------------+
        # ticket::STATUS_CLOSED -----------------------------------------------------------------------+    |
        #                                                                                              v    v
        $subtask_ids = new DBView("SELECT subtask_id FROM [wait] WHERE task_id = ?", [$ticket->id]);
        
        while ($subtask_ids->next()) {
            $subtask = ticket::getById($subtask_ids->subtask_id);
            if ($subtask->type == ticket::TYPE_REGULAR_TASK && $subtask->status != ticket::STATUS_CLOSED && $subtask->status != ticket::STATUS_ARCHIVED) {
                $subtask->setType(ticket::TYPE_PRIORITY_TASK);
            }
        }
        
    }
}
