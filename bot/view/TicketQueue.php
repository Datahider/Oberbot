<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBView;
use losthost\DB\DBValue;
use losthost\Oberbot\data\ticket;
use losthost\DB\DB;

class TicketQueue {
    
    const SQL_GET_TICKET_QUEUE = <<<FIN
            SELECT 
                ticket.id AS id,
                ticket.topic_title AS title,
                CASE
                    WHEN ticket.status = 0 THEN 1
                    WHEN ticket.status = 89 THEN 2
                    WHEN ticket.status = 102 THEN 3
                    ELSE 4
                END AS status_order,
                COUNT(agent.topic_number) AS agent_count,
                COUNT(iagent.topic_number) AS amiagent,
                COUNT(subtask.id) AS subtask_count
            FROM 
                    [topics] AS ticket
                    LEFT JOIN [topic_admins] AS agent
                        ON (agent.topic_number = ticket.id)
                    LEFT JOIN [topic_admins] AS iagent
                        ON (iagent.topic_number = ticket.id AND iagent.user_id = :user_id)
                    LEFT JOIN [wait] AS wait
                        ON wait.task_id = ticket.id
                    LEFT JOIN [topics] AS subtask
                        ON subtask.id = wait.subtask_id AND subtask.status IN (0, 1, 88, 89, 102)
                        
            WHERE 
                ticket.status IN (0, 1, 89, 102) /* Все открытые */
                AND (ticket.wait_till IS NULL OR ticket.wait_till < :now)
                AND ticket.chat_id IN (      /* Чат в нужном списке и пользователь агент в этом чате */
                    SELECT 
                        role.chat_id 
                    FROM
                        [user_chat_role] AS role 
                        LEFT JOIN [chat_groups] AS list 
                            ON list.chat_id = role.chat_id
                    WHERE
                        role.role = 'agent'
                        AND (:list_name = 'all' OR list.chat_group = :list_name)
                        AND role.user_id = :user_id
                )
            GROUP BY
                id, title, status_order
            HAVING
            	(agent_count = 0 OR amiagent > 0) AND subtask_count = 0
            ORDER BY
                ticket.is_task,
                status_order - ticket.is_urgent,
                ticket.last_admin_activity,
                ticket.last_activity
            FIN;
    
    static public function getQueue(int $user_id, string $list, int $length = 1) : array {
     
        $sql = static::SQL_GET_TICKET_QUEUE. " LIMIT $length";
        
        $queue = new DBView($sql, ['user_id' => $user_id, 'list_name' => $list, 'now' => date_create()->format(DB::DATE_FORMAT)]);
        
        $result = [];
        
        while ($queue->next()) {
            $result[] = ticket::getById($queue->id);
        }
        
        return $result;
    }
    
    static public function getQueueLen(int $user_id, string $list) : int {
        
        DB::query('DROP TEMPORARY TABLE IF EXISTS vt_queue');
        DB::query('CREATE TEMPORARY TABLE vt_queue '. static::SQL_GET_TICKET_QUEUE);
        $count = new DBValue('SELECT COUNT(*) AS value FROM vt_queue');
        DB::query('DROP TEMPORARY TABLE IF EXISTS vt_queue');

        return $count->value;
    }
    
}
