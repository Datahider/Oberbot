<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

use RuntimeException;

final class AiHandoffService
{
    public function __construct(
        private AiTicketLogStoreInterface $logs,
        private AiAttachmentPublisherInterface $attachments,
        private AiTransportSubmitterInterface $transport,
        private string $temp_dir
    ) {}

    public const PROVIDERS = ['codex', 'claude'];

    public function execute(int $ticket_id, string $source_chat_id, int $source_thread_id, string $ticket_title, ?string $provider = null): string
    {
        if ($provider !== null && !in_array($provider, self::PROVIDERS, true)) {
            throw new \InvalidArgumentException('Unknown AI provider: ' . $provider);
        }
        if ($this->logs->isCompleted($ticket_id)) return 'repeated';
        $published = [];
        foreach ($this->logs->entries($ticket_id) as $entry) {
            $urls = [];
            foreach ($entry->attachments as $attachment) $urls[] = $this->attachments->publish($attachment);
            $published[] = ['entry' => $entry, 'urls' => $urls];
        }

        if (!is_dir($this->temp_dir) && !mkdir($this->temp_dir, 0770, true) && !is_dir($this->temp_dir)) {
            throw new RuntimeException('Cannot create AI handoff temporary directory');
        }
        $log_path = rtrim($this->temp_dir, '/') . '/oberbot-ticket-' . $ticket_id . '-' . bin2hex(random_bytes(6)) . '.txt';
        $contents = (new AiTicketLogFormatter())->format($published);
        if (file_put_contents($log_path, $contents, LOCK_EX) === false) throw new RuntimeException('Cannot create AI ticket log file');

        try {
            $job = [
                'ticket_id' => $ticket_id,
                'source_chat_id' => $source_chat_id,
                'source_thread_id' => $source_thread_id,
                'ticket_title' => $ticket_title,
                'log_path' => $log_path,
            ];
            if ($provider !== null) $job['provider'] = $provider;
            $hidden_thread_id = $this->transport->submit($job);
            $this->logs->markCompleted($ticket_id, $hidden_thread_id);
        } catch (\Throwable $error) {
            @unlink($log_path);
            throw $error;
        }
        @unlink($log_path);
        return 'ok';
    }
}
