<?php

declare(strict_types=1);

namespace losthost\Oberbot\controller\command;

final class CommandCodex extends CommandAi
{
    public const COMMAND = 'codex';

    public static function description(): array
    {
        return ['default' => 'Передать заявку AI-агенту Codex', 'all_group_chats' => 'Передать заявку AI-агенту Codex'];
    }

    protected static function provider(): ?string
    {
        return 'codex';
    }
}
