<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

final class AiTicketLogFormatter
{
    /** @param list<array{entry:AiTicketLogEntry,urls:list<string>}> $published */
    public function format(array $published): string
    {
        $blocks = [];
        foreach ($published as $item) {
            $entry = $item['entry'];
            $lines = ['[' . $entry->created_at->format('Y-m-d H:i:s') . '] ' . $entry->role];
            if (trim($entry->text) !== '') $lines[] = trim($entry->text);
            foreach ($item['urls'] as $url) $lines[] = 'Attachment: ' . $url;
            $blocks[] = implode("\n", $lines);
        }
        return implode("\n\n", $blocks) . "\n";
    }
}
