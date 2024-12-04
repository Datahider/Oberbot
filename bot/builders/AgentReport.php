<?php

namespace losthost\Oberbot\builders;

class AgentReport extends TimeReport {
    
    protected function getSql() {
        return <<<FIN
            SELECT 
                timers.subject AS project,
                events.project AS object,
                chat.title AS topic_title,
                IFNULL(
                    SUM(
                        CASE
                            WHEN events.end_time IS NULL THEN TIMESTAMPDIFF(SECOND, GREATEST(:period_start, events.start_time), :period_end) 
                            ELSE TIMESTAMPDIFF(SECOND, GREATEST(:period_start, events.start_time), LEAST(:period_end, events.end_time)) 
                        END
                    ), 
                    0) AS total_seconds
            FROM 
                [timer_events] AS events
                LEFT JOIN [timers] AS timers ON timers.id = events.timer
                LEFT JOIN [telle_chats] AS chat ON chat.id = events.project
            WHERE 
                (:project = 'any' OR timers.subject = :project)
                AND end_time >= :period_start
                AND start_time <= :period_end
            GROUP BY 
                timers.subject,
                events.project,
                chat.title
            ORDER BY total_seconds DESC
            FIN;
    }
}
