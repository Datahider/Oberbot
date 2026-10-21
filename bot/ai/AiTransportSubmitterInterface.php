<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

interface AiTransportSubmitterInterface
{
    /** @param array<string, mixed> $job */
    public function submit(array $job): int;
}
