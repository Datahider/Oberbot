<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBView;
use losthost\DB\DBValue;
use losthost\Oberbot\data\ticket;
use losthost\DB\DB;

class TicketQueue {
    
    const SQL_CREATE_TEMP_TABLES = <<<FIN

            DROP TABLE IF EXISTS vt_active_tickets;
            DROP TABLE IF EXISTS vt_user_priorities_by_chat_id;
            DROP TABLE IF EXISTS vt_main_query;

            CREATE TEMPORARY TABLE vt_active_tickets SELECT
                    ticket.id,
                    ticket.chat_id,
                    ticket.user_priority,
                    ticket.type,
                    COUNT(subtask.id) AS subtask_count
            FROM
                [topics] AS ticket
                LEFT JOIN [wait] AS wait
                    ON wait.task_id = ticket.id
                LEFT JOIN [topics] AS subtask
                    ON subtask.id = wait.subtask_id AND subtask.status IN (0, 1, 88, 89, 102)
            WHERE
                 ticket.status IN (0, 1, 89, 102) /* Все открытые */
                 AND (ticket.wait_till IS NULL OR ticket.wait_till < :now) /* Не отложенные */
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
                    id, chat_id, user_priority, type
            HAVING
                    subtask_count = 0;


            CREATE TEMPORARY TABLE vt_user_priorities_by_chat_id SELECT
                    ticket.chat_id AS chat_id,
                    ticket.type AS type,
                MIN(ticket.user_priority) AS active_priority
            FROM
                    vt_active_tickets AS ticket
            GROUP BY
                    ticket.chat_id, ticket.type;

            
            CREATE TEMPORARY TABLE vt_main_query SELECT 
                ticket.id AS id,
                ticket.topic_title AS title,
                ticket.is_task AS is_task,
                ticket.user_priority AS user_priority,
                ticket.nice + chat.nice AS nice,
                pbychat.active_priority AS active_priority,
                CASE
                        WHEN ticket.type = 5 THEN 0 /* TYPE_URGENT_CONSULT */
                        WHEN ticket.type = 7 THEN 1 /* TYPE_MALFUNCTION_FREE */
                        WHEN ticket.type = 6 THEN 2 /* TYPE_MALFUNCTION_MULTIUSER */
                        WHEN ticket.type = 4 THEN 3 /* TYPE_SCHEDULED_CONSULT */
                        WHEN ticket.type = 3 THEN 4 /* TYPE_MALFUNCTION */
                        WHEN ticket.type = 2 THEN 5 /* TYPE_PRIORITY_TASK */
                        ELSE 6                      /* TYPE_REGULAR_TASK */
                    END AS type_order, 
                CASE
                    WHEN ticket.status = 0 THEN 1
                    WHEN ticket.status = 89 THEN 2
                    WHEN ticket.status = 102 THEN 3
                    ELSE 4
                END AS status_order,
                :time - GREATEST(ticket.last_activity, ticket.last_admin_activity) AS waiting_seconds,
                COUNT(agent.topic_number) AS agent_count,
                COUNT(iagent.topic_number) AS amiagent
            FROM 
                [topics] AS ticket
                LEFT JOIN [chat] AS chat
                    ON chat.id = ticket.chat_id
                LEFT JOIN [topic_admins] AS agent
                    ON (agent.topic_number = ticket.id)
                LEFT JOIN [topic_admins] AS iagent
                    ON (iagent.topic_number = ticket.id AND iagent.user_id = :user_id)
                LEFT JOIN vt_user_priorities_by_chat_id AS pbychat
                        ON pbychat.chat_id = ticket.chat_id AND pbychat.type = ticket.type

            WHERE 
                    ticket.id IN (SELECT id FROM vt_active_tickets)
                 AND ticket.user_priority = pbychat.active_priority
            GROUP BY
                    id, title, status_order
            HAVING
                    agent_count = 0 OR amiagent > 0;

            FIN;
    
    const SQL_DROP_TEMP_TABLES = <<<FIN
            DROP TABLE IF EXISTS vt_active_tickets;
            DROP TABLE IF EXISTS vt_user_priorities_by_chat_id;
            DROP TABLE IF EXISTS vt_main_query;
            FIN;

    const SQL_GET_TICKET_QUEUE = 'SELECT * FROM vt_main_query ORDER BY type_order + status_order - nice, waiting_seconds/86400 DESC /* 24 hours */';
    
    const SQL_GET_QUEUE_LEN = 'SELECT COUNT(*) AS value FROM vt_main_query';

    static public function getQueue(int $user_id, ?string $list, ?int $length = 1) : array {
     
        $list_name = is_string($list) ? $list : 'all';
        $sql = static::SQL_CREATE_TEMP_TABLES;
        $sql = str_replace(':user_id', $user_id, $sql);
        $sql = str_replace(':now', "'". date_create()->format(DB::DATE_FORMAT). "'", $sql);
        $sql = str_replace(':time', time(), $sql);
        $sql = str_replace(':list_name', "'$list_name'", $sql);
        DB::query($sql);

        $sql = static::SQL_GET_TICKET_QUEUE;
        if (is_numeric($length)) {
            $sql .= " LIMIT $length";
        }
        
        $queue = new DBView($sql);
        DB::query(static::SQL_DROP_TEMP_TABLES);
        $result = [];
        
        while ($queue->next()) {
            $result[] = ticket::getById($queue->id);
        }
        
        return $result;
    }
    
    static public function getQueueLen(int $user_id, string $list) : int {
        
        $sql = static::SQL_CREATE_TEMP_TABLES;
        $sql = str_replace(':user_id', $user_id, $sql);
        $sql = str_replace(':now', "'". date_create()->format(DB::DATE_FORMAT). "'", $sql);
        $sql = str_replace(':time', time(), $sql);
        $sql = str_replace(':list_name', "'$list'", $sql);
        $sql = str_replace('AND ticket.user_priority = pbychat.active_priority', '', $sql);
        DB::query($sql);
        
        $count = new DBValue(static::SQL_GET_QUEUE_LEN);
        DB::query(static::SQL_DROP_TEMP_TABLES);

        return $count->value;
    }
    
}
