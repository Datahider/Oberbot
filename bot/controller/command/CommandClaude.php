<?php

declare(strict_types=1);

namespace losthost\Oberbot\controller\command;

final class CommandClaude extends CommandAi
{
    public const COMMAND = 'claude';

    public static function description(): array
    {
        return ['default' => 'Передать заявку AI-агенту Claude', 'all_group_chats' => 'Передать заявку AI-агенту Claude'];
    }

    protected static function provider(): ?string
    {
        return 'claude';
    }
}
