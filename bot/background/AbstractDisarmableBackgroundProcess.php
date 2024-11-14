<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\DB\DBView;
use losthost\telle\model\DBPendingJob;
use losthost\telle\Bot;

abstract class AbstractDisarmableBackgroundProcess extends AbstractBackgroundProcess {
    
    static public function disarm($param) {
        
        $job_id = new DBView(
                'SELECT id AS value FROM [telle_pending_jobs] WHERE job_class = ? AND job_args = ?', 
                [static::class, $param]);

        while ($job_id->next()) {
            $job = new DBPendingJob($job_id->value);
            $job->delete();
            Bot::logComment("Pending job id:$job_id->value is deleted.");
        }
    }
}
