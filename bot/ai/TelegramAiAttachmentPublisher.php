<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

use RuntimeException;
use TelegramBot\Api\BotApi;

final class TelegramAiAttachmentPublisher implements AiAttachmentPublisherInterface
{
    public function __construct(
        private BotApi $telegram,
        private string $base_url,
        private string $token,
        private string $temp_dir
    ) {
        $this->base_url = rtrim(trim($this->base_url), '/');
        $this->token = trim($this->token);
        if ($this->base_url === '' || $this->token === '') throw new RuntimeException('AI file exchange configuration is incomplete');
    }

    public function publish(array $attachment): string
    {
        $file_id = trim((string) ($attachment['file_id'] ?? ''));
        $filename = basename(trim((string) ($attachment['name'] ?? '')));
        $mime = trim((string) ($attachment['mime'] ?? 'application/octet-stream'));
        if ($file_id === '' || $filename === '') throw new RuntimeException('Invalid AI attachment metadata');
        if (!is_dir($this->temp_dir) && !mkdir($this->temp_dir, 0770, true) && !is_dir($this->temp_dir)) {
            throw new RuntimeException('Cannot create AI temporary directory');
        }
        $temp_path = tempnam($this->temp_dir, 'oberbot-ai-file-');
        if ($temp_path === false) throw new RuntimeException('Cannot create AI attachment file');
        try {
            $contents = $this->telegram->downloadFile($file_id);
            if (!is_string($contents) || file_put_contents($temp_path, $contents, LOCK_EX) === false) {
                throw new RuntimeException('Cannot download Telegram attachment');
            }
            return $this->upload($temp_path, $filename, $mime);
        } finally {
            @unlink($temp_path);
        }
    }

    private function upload(string $file_path, string $filename, string $mime): string
    {
        $curl = curl_init($this->base_url . '/up');
        if ($curl === false) throw new RuntimeException('Cannot initialize AI attachment upload');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->token, 'Accept: application/json'],
            CURLOPT_POSTFIELDS => ['file' => new \CURLFile($file_path, $mime, $filename)],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 120,
        ]);
        $raw = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if (!is_string($raw)) throw new RuntimeException('AI attachment upload failed: ' . $error);
        $response = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $url = trim((string) ($response['url'] ?? ''));
        if ($status < 200 || $status >= 300 || $url === '') throw new RuntimeException('AI attachment upload returned invalid response');
        return $url;
    }
}
