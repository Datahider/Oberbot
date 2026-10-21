<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

interface AiAttachmentPublisherInterface
{
    /** @param array<string, mixed> $attachment */
    public function publish(array $attachment): string;
}
