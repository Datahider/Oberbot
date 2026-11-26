<?php

declare(strict_types=1);

namespace losthost\Oberbot\api;

interface ApiTicketRequestStoreInterface
{
    /** @return array{chat_id:int,user_id:int,title:string,text:string,ticket_id:?int,error:?string} */
    public function load(int $request_id): array;

    public function markCreated(int $request_id, int $ticket_id): void;

    public function markFailed(int $request_id, string $error): void;
}
