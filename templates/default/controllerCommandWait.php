<?php

use losthost\DB\DBView;
use losthost\Oberbot\data\ticket;

use function losthost\Oberbot\__;
use function losthost\Oberbot\ticketMentionNoId;

if ($ticket->wait_till) {
    $wait_till = date_create($ticket->wait_till);
    if ($wait_till->getTimestamp() < date_create()->getTimestamp()) {
        $wait_till = null;
    }
} else {
    $wait_till = null;
}

$subtask_view = new DBView(<<<FIN
        SELECT subtask_id 
        FROM [wait] AS w 
        LEFT JOIN [topics] AS t 
            ON t.id = w.subtask_id 
        WHERE 
            task_id = ? 
            AND t.status NOT IN (111, 120)
        FIN, [$ticket->id]);

$subtasks = [];

while ($subtask_view->next()) {
    $subtasks[] = ticket::getById($subtask_view->subtask_id);
}

if (empty($wait_till) && empty($subtasks)) {
    echo __("Эта %entity% не была отложена.", ['entity' => $ticket->entityName()]);
} else {
    $text = __("Эта %entity% отложена\n", ['entity' => $ticket->entityName()]);
    if ($wait_till) {
        $text .= __("- до %till%\n", ['till' => $wait_till->format('d-m-Y H:i')]);
    }
    foreach ($subtasks as $subtask) {
        $text .= __("- до решения %subtask%\n", ['subtask' => ticketMentionNoId($subtask)]);
    }
    echo $text;
}