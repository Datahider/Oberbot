<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use losthost\Oberbot\api\ApiTicketCreatorInterface;
use losthost\Oberbot\api\ApiTicketRequestProcessor;
use losthost\Oberbot\api\ApiTicketRequestStoreInterface;
use losthost\Oberbot\background\CreateTicketFromApi;
use losthost\Oberbot\data\api_ticket_request;
use losthost\Oberbot\data\apikey;
use losthost\Oberbot\data\chat_apikey;

function apiAssertSame(mixed $expected, mixed $actual, string $label): void
{
    if ($expected !== $actual) throw new RuntimeException($label . ': expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
}

function apiAssertThrows(callable $callback, string $label): void
{
    try {
        $callback();
    } catch (Throwable) {
        return;
    }
    throw new RuntimeException($label . ': exception expected');
}

final class ApiMemoryRequestStore implements ApiTicketRequestStoreInterface
{
    public array $requests = [];
    public function load(int $request_id): array
    {
        if (!isset($this->requests[$request_id])) throw new RuntimeException('not found');
        return $this->requests[$request_id];
    }
    public function markCreated(int $request_id, int $ticket_id): void { $this->requests[$request_id]['ticket_id'] = $ticket_id; }
    public function markFailed(int $request_id, string $error): void { $this->requests[$request_id]['error'] = $error; }
}

final class ApiFakeCreator implements ApiTicketCreatorInterface
{
    public bool $fail = false;
    public array $calls = [];
    public function create(int $chat_id, int $user_id, string $title, string $text): int
    {
        $this->calls[] = [$chat_id, $user_id, $title, $text];
        if ($this->fail) throw new RuntimeException(str_repeat('x', 600));
        return 41600;
    }
}

$store = new ApiMemoryRequestStore();
$store->requests[7] = ['chat_id' => -1001631150452, 'user_id' => 203645978, 'title' => 'Ордер не проведён', 'text' => 'Текст ошибки', 'ticket_id' => null, 'error' => null];
$creator = new ApiFakeCreator();
$processor = new ApiTicketRequestProcessor($store, $creator);

apiAssertSame(41600, $processor->process(7), 'processor returns created ticket id');
apiAssertSame([[-1001631150452, 203645978, 'Ордер не проведён', 'Текст ошибки']], $creator->calls, 'creator receives request data');
apiAssertSame(41600, $store->requests[7]['ticket_id'], 'ticket id is stored');

apiAssertThrows(static fn () => $processor->process(7), 'second processing of created request is rejected');
apiAssertSame(1, count($creator->calls), 'creator is not called for created request');

$store->requests[8] = ['chat_id' => -100, 'user_id' => 1, 'title' => 't', 'text' => 'x', 'ticket_id' => null, 'error' => null];
$creator->fail = true;
apiAssertThrows(static fn () => $processor->process(8), 'creator error is rethrown');
apiAssertSame(500, mb_strlen($store->requests[8]['error']), 'error is stored truncated to 500 chars');
apiAssertSame(null, $store->requests[8]['ticket_id'], 'failed request has no ticket');

apiAssertSame(true, is_subclass_of(CreateTicketFromApi::class, \losthost\telle\abst\AbstractBackgroundProcess::class), 'background process class exists');

foreach (['id', 'secret', 'user_id', 'comment', 'UNIQUE INDEX secret'] as $field) {
    apiAssertSame(true, array_key_exists($field, apikey::METADATA), "apikey.$field");
}
apiAssertSame(true, str_contains(chat_apikey::METADATA['permissions'], '"create_ticket"'), 'chat_apikey permission create_ticket');
apiAssertSame(true, array_key_exists('UNIQUE INDEX apikey_chat', chat_apikey::METADATA), 'chat_apikey unique key+chat');
foreach (['id', 'apikey_id', 'chat_id', 'user_id', 'title', 'text', 'created', 'ticket_id', 'error'] as $field) {
    apiAssertSame(true, array_key_exists($field, api_ticket_request::METADATA), "api_ticket_request.$field");
}

$index = file_get_contents(__DIR__ . '/../index.php');
foreach (['apikey', 'chat_apikey', 'api_ticket_request'] as $class) {
    apiAssertSame(true, str_contains($index, "\\losthost\\Oberbot\\data\\$class::initDataStructure();"), "$class table is initialized");
}

echo "ApiTicketTest OK\n";
