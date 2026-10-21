<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

use TelegramBot\Api\Types\Message;

final class AiTelegramMessageExtractor
{
    public function extract(Message $message, string $role): ?AiTicketLogEntry
    {
        $from = $message->getFrom();
        if ($from === null || $from->isBot()) return null;
        $text = (string) ($message->getText() ?? $message->getCaption() ?? '');
        if (preg_match('/^\s*\//u', $text) === 1) return null;
        $attachments = $this->attachments($message);
        if (trim($text) === '' && $attachments === []) return null;
        return new AiTicketLogEntry(
            (new \DateTimeImmutable())->setTimestamp((int) $message->getDate()),
            $role,
            $text,
            $attachments
        );
    }

    /** @return list<array<string, mixed>> */
    private function attachments(Message $message): array
    {
        if ($message->getPhoto()) {
            $photos = $message->getPhoto();
            usort($photos, static fn ($a, $b) => ((int) $a->getFileSize()) <=> ((int) $b->getFileSize()));
            $photo = end($photos);
            return [$this->metadata('photo', $photo, 'photo-' . $photo->getFileId() . '.jpg', 'image/jpeg')];
        }
        $candidates = [
            ['document', $message->getDocument(), 'document.bin', 'application/octet-stream'],
            ['audio', $message->getAudio(), 'audio.bin', 'audio/mpeg'],
            ['video', $message->getVideo(), 'video.mp4', 'video/mp4'],
            ['voice', $message->getVoice(), 'voice.ogg', 'audio/ogg'],
            ['animation', $message->getAnimation(), 'animation.gif', 'image/gif'],
            ['video_note', $message->getVideoNote(), 'video-note.mp4', 'video/mp4'],
            ['sticker', $message->getSticker(), 'sticker.bin', 'application/octet-stream'],
        ];
        foreach ($candidates as [$type, $file, $default_name, $default_mime]) {
            if ($file !== null) return [$this->metadata($type, $file, $default_name, $default_mime)];
        }
        return [];
    }

    /** @return array<string, mixed> */
    private function metadata(string $type, object $file, string $default_name, string $default_mime): array
    {
        $name = method_exists($file, 'getFileName') ? trim((string) $file->getFileName()) : '';
        $mime = method_exists($file, 'getMimeType') ? trim((string) $file->getMimeType()) : '';
        return [
            'type' => $type,
            'file_id' => (string) $file->getFileId(),
            'name' => $name !== '' ? $name : $default_name,
            'mime' => $mime !== '' ? $mime : $default_mime,
            'size_bytes' => method_exists($file, 'getFileSize') ? (int) $file->getFileSize() : 0,
        ];
    }
}
