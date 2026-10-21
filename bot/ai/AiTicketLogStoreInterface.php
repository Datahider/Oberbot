<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

interface AiTicketLogStoreInterface
{
    public function append(int $ticket_id, string $source_chat_id, int $message_id, AiTicketLogEntry $entry): void;
    /** @return list<AiTicketLogEntry> */
    public function entries(int $ticket_id): array;
    public function isCompleted(int $ticket_id): bool;
    public function markCompleted(int $ticket_id, int $hidden_thread_id): void;
    public function cleanup(int $ticket_id): void;
}
