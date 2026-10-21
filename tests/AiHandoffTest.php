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
aiAssertSame('repeated', $service->execute(42, '-10037366278346', 55, 'Не работает 1С'), 'repeat command');

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
