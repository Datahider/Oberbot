<?php

declare(strict_types=1);

namespace losthost\Oberbot\api;

interface ApiTicketCreatorInterface
{
    public function create(int $chat_id, int $user_id, string $title, string $text): int;
}
