<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\DB\DBView;
use losthost\telle\model\DBPendingJob;
use losthost\telle\Bot;
use losthost\DB\DB;

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
    
    static public function changeTime($param, \DateTime|\DateTimeImmutable $new_time) {
        
        $sth = DB::prepare("UPDATE [telle_pending_jobs] SET start_time = :new_start_time WHERE job_class = :job_class AND job_args = :job_args AND was_started IS NULL"); 
        $sth->execute([
            'new_start_time' => $new_time->format(DB::DATE_FORMAT),
            'job_class' => static::class,
            'job_args' => $param,
        ]);
    }
}
