<?php

namespace losthost\Oberbot\background;

use losthost\DB\DBTracker;
use losthost\Oberbot\data\ticket;
use losthost\telle\model\DBPendingJob;
use losthost\DB\DBView;
use losthost\telle\Bot;

class DestroyIncompleteTimer extends DBTracker {
    
    public function track(\losthost\DB\DBEvent $event) {
    
        $ticket = $event->object;
        
        if (array_search('status', $event->fields) !== false && $ticket->status != ticket::STATUS_CREATING) {
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
}
