<?php

declare(strict_types=1);

namespace losthost\Oberbot\api;

final class ApiTicketRequestProcessor
{
    public function __construct(
        private readonly ApiTicketRequestStoreInterface $store,
        private readonly ApiTicketCreatorInterface $creator,
    ) {
    }

    public function process(int $request_id): int
    {
        $request = $this->store->load($request_id);
        if ($request['ticket_id'] !== null) {
            throw new \RuntimeException("Ticket request $request_id already has ticket {$request['ticket_id']}");
        }

        try {
            $ticket_id = $this->creator->create($request['chat_id'], $request['user_id'], $request['title'], $request['text']);
        } catch (\Throwable $e) {
            $this->store->markFailed($request_id, mb_substr($e->getMessage(), 0, 500));
            throw $e;
        }

        $this->store->markCreated($request_id, $ticket_id);
        return $ticket_id;
    }
}
