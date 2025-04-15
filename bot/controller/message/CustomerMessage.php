<?php

namespace losthost\Oberbot\controller\message;

use losthost\Oberbot\service\Service;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\ticket;
use losthost\telle\model\DBPendingJob;
use losthost\Oberbot\background\CloseIncompleteTicket;
use losthost\DB\DBView;

class CustomerMessage extends AbstractMemberMessage {
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        if (Service::isAgent($user_id, $group_id)) {
            return false;
        }
        
        $ticket = ticket::getByGroupThread($group_id, $thread_id);
        
        $view = new BotView(Bot::$api, $group_id, Bot::$language_code);
        
        if ($ticket->status == ticket::STATUS_CLOSED) {
            $view->show('controllerCustomerMessageClosed', 'ctrlkbdCustomerMessageClosed', [], null, $thread_id);
        } elseif ($ticket->status == ticket::STATUS_AWAITING_USER) {
            $ticket->userAnswered();
        } elseif ($ticket->status == ticket::STATUS_REOPEN) {
            $this->destroyIncompleteTimer($ticket);
        }
        
        return true;
    }
    
    protected function destroyIncompleteTimer($ticket) {
        
        $job_id = new DBView(
                'SELECT id AS value FROM [telle_pending_jobs] WHERE job_class = ? AND job_args = ?', 
                [CloseIncompleteTicket::class, $ticket->id]);

        if ($job_id->next()) {
            $job = new DBPendingJob($job_id->value);
            $job->delete();
            Bot::logComment("Pending job id:$job_id->value is deleted.");
        }
    }
}
