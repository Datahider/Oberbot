<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use losthost\Oberbot\ai\AiAttachmentPublisherInterface;
use losthost\Oberbot\ai\AiHandoffService;
use losthost\Oberbot\ai\AiTicketLogEntry;
use losthost\Oberbot\ai\AiTicketLogStoreInterface;
use losthost\Oberbot\ai\AiTransportSubmitterInterface;

function aiAssertSame(mixed $expected, mixed $actual, string $label): void
{
    if ($expected !== $actual) throw new RuntimeException($label . ': expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
}

final class AiMemoryLogStore implements AiTicketLogStoreInterface
{
    public bool $completed = false;
    public bool $cleaned = false;
    public array $entries = [];
    public function append(int $ticket_id, string $source_chat_id, int $message_id, AiTicketLogEntry $entry): void { $this->entries[] = $entry; }
    public function entries(int $ticket_id): array { return $this->entries; }
    public function isCompleted(int $ticket_id): bool { return $this->completed; }
    public function markCompleted(int $ticket_id, int $hidden_thread_id): void { $this->completed = true; }
    public function cleanup(int $ticket_id): void { $this->cleaned = true; }
}

final class AiFakePublisher implements AiAttachmentPublisherInterface
{
    public function publish(array $attachment): string { return 'https://files.ioannidis.ru/' . $attachment['file_id']; }
}

final class AiFakeSubmitter implements AiTransportSubmitterInterface
{
    public bool $fail = false;
    public array $jobs = [];
    public array $logs = [];
    public function submit(array $job): int
    {
        $this->jobs[] = $job;
        $this->logs[] = file_get_contents($job['log_path']);
        if ($this->fail) throw new RuntimeException('failed');
        return 771;
    }
}

$store = new AiMemoryLogStore();
$store->entries = [
    new AiTicketLogEntry(new DateTimeImmutable('2026-10-21 10:00:00'), 'Клиент', 'Не запускается', [['file_id' => 'screen1', 'name' => 'screen.png']]),
    new AiTicketLogEntry(new DateTimeImmutable('2026-10-21 10:01:00'), 'Агент', 'Сейчас проверю', []),
];
$submitter = new AiFakeSubmitter();
$service = new AiHandoffService($store, new AiFakePublisher(), $submitter, '/home/web/tmp');

$result = $service->execute(42, '-10037366278346', 55, 'Не работает 1С');
aiAssertSame('ok', $result, 'successful command');
aiAssertSame(true, $store->completed, 'completed marker');
$log = $submitter->logs[0];
aiAssertSame(true, str_contains((string) $log, '[2026-10-21 10:00:00] Клиент'), 'client line');
aiAssertSame(true, str_contains((string) $log, 'https://files.ioannidis.ru/screen1'), 'attachment URL');
aiAssertSame(false, file_exists($submitter->jobs[0]['log_path']), 'successful handoff log removed');
aiAssertSame(false, array_key_exists('provider', $submitter->jobs[0]), '/ai job has no provider');
aiAssertSame('repeated', $service->execute(42, '-10037366278346', 55, 'Не работает 1С'), 'repeat command');
aiAssertSame('repeated', $service->execute(42, '-10037366278346', 55, 'Не работает 1С', 'claude'), 'repeat with provider');
aiAssertSame(1, count($submitter->jobs), 'repeat does not call transport');

foreach (['codex', 'claude'] as $provider) {
    $provider_store = new AiMemoryLogStore();
    $provider_store->entries = $store->entries;
    $provider_submitter = new AiFakeSubmitter();
    $provider_service = new AiHandoffService($provider_store, new AiFakePublisher(), $provider_submitter, '/home/web/tmp');
    aiAssertSame('ok', $provider_service->execute(44, '-100', 57, 'Провайдер', $provider), $provider . ' handoff');
    aiAssertSame($provider, $provider_submitter->jobs[0]['provider'] ?? null, $provider . ' job provider');
}

try {
    (new AiHandoffService(new AiMemoryLogStore(), new AiFakePublisher(), new AiFakeSubmitter(), '/home/web/tmp'))->execute(45, '-100', 58, 'Ошибка', 'gemini');
    throw new LogicException('unknown provider must be rejected');
} catch (InvalidArgumentException $error) {
    aiAssertSame('Unknown AI provider: gemini', $error->getMessage(), 'unknown provider rejected');
}

$commands = [
    'ai' => losthost\Oberbot\controller\command\CommandAi::class,
    'codex' => losthost\Oberbot\controller\command\CommandCodex::class,
    'claude' => losthost\Oberbot\controller\command\CommandClaude::class,
];
foreach ($commands as $name => $class) {
    aiAssertSame($name, $class::COMMAND, $name . ' command name');
}
$logic = (string) file_get_contents(__DIR__ . '/../etc/logic.php');
$functions = (string) file_get_contents(__DIR__ . '/../bot/functions.php');
foreach (['CommandCodex', 'CommandClaude'] as $class) {
    aiAssertSame(true, str_contains($logic, 'command\\' . $class . '::class'), $class . ' handler registered');
    aiAssertSame(substr_count($functions, 'CommandAi::getBotCommand()'), substr_count($functions, $class . '::getBotCommand()'), $class . ' listed with /ai');
}

$failed_store = new AiMemoryLogStore();
$failed_store->entries = $store->entries;
$failed_submitter = new AiFakeSubmitter();
$failed_submitter->fail = true;
$failed = new AiHandoffService($failed_store, new AiFakePublisher(), $failed_submitter, '/home/web/tmp');
try {
    $failed->execute(43, '-100', 56, 'Ошибка');
    throw new RuntimeException('failure expected');
} catch (RuntimeException $error) {
    aiAssertSame(false, $failed_store->completed, 'failed handoff remains retryable');
    aiAssertSame(false, file_exists($failed_submitter->jobs[0]['log_path']), 'failed handoff log removed');
}

echo "Oberbot AI handoff tests: OK\n";
