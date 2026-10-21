<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

final class AiTicketLogEntry
{
    /** @param list<array<string, mixed>> $attachments */
    public function __construct(
        public readonly \DateTimeImmutable $created_at,
        public readonly string $role,
        public readonly string $text,
        public readonly array $attachments
    ) {}
}
