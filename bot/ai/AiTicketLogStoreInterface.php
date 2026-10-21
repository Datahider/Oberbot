<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

interface AiTicketLogStoreInterface
{
    /** @return list<AiTicketLogEntry> */
    public function entries(int $ticket_id): array;
    public function isCompleted(int $ticket_id): bool;
    public function markCompleted(int $ticket_id, int $hidden_thread_id): void;
    public function cleanup(int $ticket_id): void;
}
