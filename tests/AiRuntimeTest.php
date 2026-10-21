<?php

declare(strict_types=1);

use losthost\Oberbot\ai\AiProcessRunnerInterface;
use losthost\Oberbot\ai\AiTelegramMessageExtractor;
use losthost\Oberbot\ai\LocalAiTransportSubmitter;
use TelegramBot\Api\Types\Message;

final class AiFakeProcessRunner implements AiProcessRunnerInterface
{
    public array $command = [];
    public array $job = [];

    public function run(array $command): array
    {
        $this->command = $command;
        $this->job = json_decode((string) file_get_contents($command[3]), true, 512, JSON_THROW_ON_ERROR);
        return ['exit_code' => 0, 'stdout' => '{"accepted":true,"hidden_thread_id":771}', 'stderr' => ''];
    }
}

function aiMessage(array $extra): Message
{
    return Message::fromResponse(array_merge([
        'message_id' => 15,
        'message_thread_id' => 55,
        'date' => 1792576800,
        'chat' => ['id' => -10037366278346, 'type' => 'supergroup'],
        'from' => ['id' => 10, 'is_bot' => false, 'first_name' => 'User'],
    ], $extra));
}

$extractor = new AiTelegramMessageExtractor();
$text_entry = $extractor->extract(aiMessage(['text' => 'Не работает 1С']), 'Клиент');
aiAssertSame('Клиент', $text_entry?->role, 'client role extracted');
aiAssertSame('Не работает 1С', $text_entry?->text, 'text extracted');
aiAssertSame(null, $extractor->extract(aiMessage(['text' => '/done']), 'Клиент'), 'commands ignored');
aiAssertSame(null, $extractor->extract(aiMessage([
    'text' => 'bot text',
    'from' => ['id' => 11, 'is_bot' => true, 'first_name' => 'Bot'],
]), 'Агент'), 'bot messages ignored');

$photo_entry = $extractor->extract(aiMessage([
    'caption' => 'Скрин ошибки',
    'photo' => [
        ['file_id' => 'small', 'file_unique_id' => 's', 'width' => 90, 'height' => 90, 'file_size' => 100],
        ['file_id' => 'large', 'file_unique_id' => 'l', 'width' => 1280, 'height' => 720, 'file_size' => 5000],
    ],
]), 'Клиент');
aiAssertSame('Скрин ошибки', $photo_entry?->text, 'caption extracted');
aiAssertSame('large', $photo_entry?->attachments[0]['file_id'] ?? null, 'largest photo selected');
aiAssertSame(null, $extractor->extract(aiMessage([]), 'Клиент'), 'service message ignored');

$runner = new AiFakeProcessRunner();
$submitter = new LocalAiTransportSubmitter('/srv/ai/bin/submit-oberbot-ticket.php', '/srv/ai/etc/config.php', 'shared-secret', '/home/web/tmp', $runner);
$hidden_thread_id = $submitter->submit([
    'ticket_id' => 42,
    'source_chat_id' => '-10037366278346',
    'source_thread_id' => 55,
    'ticket_title' => 'Не работает 1С',
    'log_path' => '/home/web/tmp/log.txt',
]);
aiAssertSame(771, $hidden_thread_id, 'hidden thread returned');
aiAssertSame('php', $runner->command[0] ?? null, 'php command');
aiAssertSame('/srv/ai/bin/submit-oberbot-ticket.php', $runner->command[1] ?? null, 'submit script argument');
aiAssertSame('shared-secret', $runner->job['secret'] ?? null, 'shared secret in job');
aiAssertSame(false, file_exists($runner->command[3]), 'job file removed');

echo "Oberbot AI runtime tests: OK\n";
