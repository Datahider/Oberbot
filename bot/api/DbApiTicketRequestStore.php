<?php

declare(strict_types=1);

namespace losthost\Oberbot\api;

use losthost\Oberbot\data\api_ticket_request;

final class DbApiTicketRequestStore implements ApiTicketRequestStoreInterface
{
    public function load(int $request_id): array
    {
        $request = new api_ticket_request(['id' => $request_id]);
        return [
            'chat_id' => (int) $request->chat_id,
            'user_id' => (int) $request->user_id,
            'title' => (string) $request->title,
            'text' => (string) $request->text,
            'type' => (int) $request->type,
            'ticket_id' => $request->ticket_id === null ? null : (int) $request->ticket_id,
            'error' => $request->error,
        ];
    }

    public function markCreated(int $request_id, int $ticket_id): void
    {
        $request = new api_ticket_request(['id' => $request_id]);
        $request->ticket_id = $ticket_id;
        $request->write();
    }

    public function markFailed(int $request_id, string $error): void
    {
        $request = new api_ticket_request(['id' => $request_id]);
        $request->error = $error;
        $request->write();
    }
}
