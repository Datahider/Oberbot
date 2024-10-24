<?php

namespace losthost\Oberbot\builders;

use losthost\Oberbot\builders\AbstractBuilder;
use losthost\DB\DB;

class MdReport extends AbstractBuilder {
    
    public function build(?array $params = null): array {
        
        $this->checkBuildParams($params);
        
        $sth = DB::prepare($this->getSqlQuery());
        $sth->execute([$params['group'], $params['group']]);
        
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();
        $sth->nextRowset();

        $result = [];
        while ($row = $sth->fetch()) {
            $result[] = [
                'chat_title' => $row['chat_title'],
                'chat_id' => $row['chat_id'],
                'status' => $row['status'],
                'is_task' => $row['is_task'],
                'is_urgent' => $row['is_urgent'],
                'topic_title' => $row['topic_title'],
                'topic_id' => $row['topic_id']
            ];
        }
        
        return $result;
        
    }
    
    protected function getSqlQuery() {
        return <<<FIN
            DROP TEMPORARY TABLE IF EXISTS vt_topics;
            DROP TEMPORARY TABLE IF EXISTS vt_t2;

            CREATE TEMPORARY TABLE vt_topics
            SELECT 
                IFNULL(chat.title, 'НЕОПРЕДЕЛЕНО') AS chat_title,
                topic.chat_id AS chat_id,
                topic.status AS status,
                topic.is_task AS is_task,
                topic.is_urgent AS is_urgent,
                topic.topic_title AS topic_title,
                topic.topic_id AS topic_id,
                topic.id AS id,
                topic.wait_for AS wait_for,
                topic.wait_till AS wait_till
            FROM 
                [topics] AS topic 
                LEFT JOIN [telle_chats] AS chat ON chat.id = topic.chat_id 
            WHERE 
                topic.status < 100 
                AND topic.chat_id IN (
                        SELECT chat_group.chat_id FROM [chat_groups] AS chat_group WHERE ? = 'all' OR chat_group.chat_group = ?
                    );
        
            CREATE TEMPORARY TABLE vt_t2 SELECT id FROM vt_topics;
        
            SELECT 
                *
            FROM 
                vt_topics
            WHERE 
                (wait_for IS NULL OR wait_for NOT IN (SELECT id FROM vt_t2))
                AND (wait_till IS NULL OR wait_till < NOW())
            ORDER BY
                is_task, status, is_urgent DESC, chat_title;
        
            FIN;
    }
}
