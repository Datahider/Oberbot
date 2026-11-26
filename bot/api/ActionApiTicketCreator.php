<?php

declare(strict_types=1);

namespace losthost\Oberbot\api;

use losthost\Oberbot\controller\action\ActionCreateTicket;

final class ActionApiTicketCreator implements ApiTicketCreatorInterface
{
    public function create(int $chat_id, int $user_id, string $title, string $text): int
    {
        return (int) ActionCreateTicket::do(null, $chat_id, $user_id, $title, [$text])->id;
    }
}
