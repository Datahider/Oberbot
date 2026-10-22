<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

use losthost\DB\DBList;
use losthost\Oberbot\data\AiTicketLog;
use losthost\Oberbot\data\OberbotAiHandoff;

final class DbAiTicketLogStore implements AiTicketLogStoreInterface
{
    public function append(int $ticket_id, string $source_chat_id, int $message_id, AiTicketLogEntry $entry): void
    {
        $row = new AiTicketLog(['source_chat_id' => $source_chat_id, 'telegram_message_id' => $message_id], true);
        if (!$row->isNew()) return;
        $row->ticket_id = $ticket_id;
        $row->source_chat_id = $source_chat_id;
        $row->telegram_message_id = $message_id;
        $row->created_at = $entry->created_at;
        $row->role = $entry->role;
        $row->text = $entry->text;
        $row->attachments = json_encode($entry->attachments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $row->write();
    }

    public function entries(int $ticket_id): array
    {
        $result = [];
        $list = new DBList(AiTicketLog::class, 'ticket_id = ? ORDER BY created_at, id', [$ticket_id]);
        while (($row = $list->next()) instanceof AiTicketLog) {
            $attachments = json_decode((string) $row->attachments, true, 512, JSON_THROW_ON_ERROR);
            $result[] = new AiTicketLogEntry(
                $row->created_at,
                (string) $row->role,
                (string) $row->text,
                $attachments
            );
        }
        return $result;
    }

    public function isCompleted(int $ticket_id): bool
    {
        $row = new OberbotAiHandoff(['ticket_id' => $ticket_id], true);
        return !$row->isNew() && (bool) $row->ingress_sent;
    }

    public function markCompleted(int $ticket_id, int $hidden_thread_id): void
    {
        $row = new OberbotAiHandoff(['ticket_id' => $ticket_id]);
        if (!(bool) $row->ingress_sent || (int) $row->hidden_thread_id !== $hidden_thread_id) {
            throw new \RuntimeException('AI transport handoff state is inconsistent');
        }
    }

    public function cleanup(int $ticket_id): void
    {
        $list = new DBList(AiTicketLog::class, ['ticket_id' => $ticket_id]);
        while (($row = $list->next()) instanceof AiTicketLog) $row->delete();
    }
}
