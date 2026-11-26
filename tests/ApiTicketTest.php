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
    public function create(int $chat_id, int $user_id, string $title, string $text, int $type): int
    {
        $this->calls[] = [$chat_id, $user_id, $title, $text, $type];
        if ($this->fail) throw new RuntimeException(str_repeat('x', 600));
        return 41600;
    }
}

$store = new ApiMemoryRequestStore();
$store->requests[7] = ['chat_id' => -1001631150452, 'user_id' => 203645978, 'title' => 'Ордер не проведён', 'text' => 'Текст ошибки', 'type' => 3, 'ticket_id' => null, 'error' => null];
$creator = new ApiFakeCreator();
$processor = new ApiTicketRequestProcessor($store, $creator);

apiAssertSame(41600, $processor->process(7), 'processor returns created ticket id');
apiAssertSame([[-1001631150452, 203645978, 'Ордер не проведён', 'Текст ошибки', 3]], $creator->calls, 'creator receives request data');
apiAssertSame(41600, $store->requests[7]['ticket_id'], 'ticket id is stored');

apiAssertThrows(static fn () => $processor->process(7), 'second processing of created request is rejected');
apiAssertSame(1, count($creator->calls), 'creator is not called for created request');

$store->requests[8] = ['chat_id' => -100, 'user_id' => 1, 'title' => 't', 'text' => 'x', 'type' => 1, 'ticket_id' => null, 'error' => null];
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
foreach (['id', 'apikey_id', 'chat_id', 'user_id', 'title', 'text', 'type', 'created', 'ticket_id', 'error'] as $field) {
    apiAssertSame(true, array_key_exists($field, api_ticket_request::METADATA), "api_ticket_request.$field");
}

$index = file_get_contents(__DIR__ . '/../index.php');
foreach (['apikey', 'chat_apikey', 'api_ticket_request'] as $class) {
    apiAssertSame(true, str_contains($index, "\\losthost\\Oberbot\\data\\$class::initDataStructure();"), "$class table is initialized");
}

final class ApiFakeTelegramApi extends \losthost\telle\ExtendedApi
{
    public array $sent = [];
    public function __construct() {}
    public function sendMessage($chatId, $text, $parseMode = null, $disablePreview = false, $replyToMessageId = null, $replyMarkup = null, $disableNotification = false, $messageThreadId = null, $protectContent = null, $allowSendingWithoutReply = null, $replyParameters = null, $linkPreviewOptions = null)
    {
        $this->sent[] = [$chatId, $text, $messageThreadId];
        $message = new \TelegramBot\Api\Types\Message();
        $message->setMessageId(501);
        return $message;
    }
}

$fake_api = new ApiFakeTelegramApi();
\losthost\telle\Bot::$api = $fake_api;
apiAssertSame(false, (new ReflectionProperty(\losthost\telle\Bot::class, 'chat'))->isInitialized(), 'Bot::$chat is not initialized in background');
apiAssertSame(501, \losthost\Oberbot\sendMessage('Текст', null, -1001631150452, 77), 'sendMessage with explicit chat works without Bot::$chat');
apiAssertSame([[-1001631150452, 'Текст', 77]], $fake_api->sent, 'sendMessage uses explicit chat');

$create_ticket = new ReflectionMethod(\losthost\Oberbot\data\ticket::class, 'create');
apiAssertSame('type', $create_ticket->getParameters()[4]->getName() ?? null, 'ticket::create accepts type');
apiAssertSame(\losthost\Oberbot\data\topic::TYPE_REGULAR_TASK, $create_ticket->getParameters()[4]->getDefaultValue(), 'ticket::create defaults to regular task');
$action_do = new ReflectionMethod(\losthost\Oberbot\controller\action\ActionCreateTicket::class, 'do');
apiAssertSame('type', $action_do->getParameters()[5]->getName(), 'ActionCreateTicket::do accepts type');
apiAssertSame(\losthost\Oberbot\data\topic::TYPE_REGULAR_TASK, $action_do->getParameters()[5]->getDefaultValue(), 'ActionCreateTicket::do defaults to regular task');
$ticket_source = file_get_contents(__DIR__ . '/../bot/data/ticket.php');
apiAssertSame(true, str_contains($ticket_source, '$ticket->type = $type;'), 'ticket::create writes given type on insert');

echo "ApiTicketTest OK\n";
