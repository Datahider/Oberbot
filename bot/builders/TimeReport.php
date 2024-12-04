<?php

namespace losthost\Oberbot\builders;

use losthost\Oberbot\builders\AbstractBuilder;
use losthost\DB\DBView;
use losthost\Oberbot\data\topic;

class TimeReport extends AbstractBuilder {
    
    protected function getSql() {
        $sql = <<<FIN
            SELECT 
                project,
                object,
                topics.topic_title AS topic_title,
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
                LEFT JOIN [topics] AS topics ON topics.id = events.object
            WHERE 
                (:project = 'any' OR project = :project)
                AND end_time >= :period_start
                AND start_time <= :period_end
            GROUP BY 
                project,
                object,
                topic_title
            ORDER BY total_seconds DESC
            FIN;
        
        return $sql;
    }
    
    public function build(?array $params = null): array {
    
        $this->checkBuildParams($params);
        
        $sql = $this->getSql();
        $view = new DBView($sql, $params);
        
        $result = [];
        while ($view->next()) {
            $result[] = (object)[
                'project' => $view->project,
                'topic_title' => $view->topic_title,
                'total_seconds' => $view->total_seconds
            ];
        }
        
        return $result;
    }
    
    protected function checkBuildParams(?array &$params) {
        parent::checkBuildParams($params);
        
        if (empty($params['project'])) {
            $params['project'] = 'any';
        }
        
        if (empty($params['period_start'])) {
            throw new \Exception('Param period_start is not defined.');
        }
        if (empty($params['period_end'])) {
            throw new \Exception('Param period_end is not defined.');
        }
    }
}
