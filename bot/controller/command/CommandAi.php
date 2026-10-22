<?php

declare(strict_types=1);

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\ai\AiRuntimeFactory;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use TelegramBot\Api\Types\Message;

use function losthost\Oberbot\sendMessage;

class CommandAi extends AbstractAuthCommand
{
    public const COMMAND = 'ai';

    protected static function permit(): int
    {
        return self::PERMIT_AGENT;
    }

    public static function description(): array
    {
        return ['default' => 'Передать заявку AI-агенту', 'all_group_chats' => 'Передать заявку AI-агенту'];
    }

    protected static function provider(): ?string
    {
        return null;
    }

    protected function handle(Message &$message): bool
    {
        try {
            $ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
            $result = AiRuntimeFactory::createHandoffService()->execute(
                (int) $ticket->id,
                (string) $ticket->chat_id,
                (int) $ticket->topic_id,
                (string) $ticket->topic_title,
                static::provider()
            );
            sendMessage($result === 'repeated' ? 'Повторная команда /' . static::COMMAND . ' запрещена' : 'Ок', null, null, $this->thread_id);
        } catch (\Throwable $error) {
            Bot::logThrowable($error, 'AI handoff command failed');
            sendMessage('Ошибка выполнения команды', null, null, $this->thread_id);
        }
        return true;
    }
}
