<?php

declare(strict_types=1);

namespace losthost\Oberbot\controller\post;

use losthost\Oberbot\ai\AiRuntimeFactory;
use losthost\Oberbot\ai\AiTelegramMessageExtractor;
use losthost\Oberbot\data\ticket;
use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use TelegramBot\Api\Types\Message;

use function losthost\Oberbot\isAgent;

final class AiTicketLogCollector extends AbstractHandlerMessage
{
    public const IS_FINAL = true;

    protected function check(Message &$message): bool
    {
        return (bool) $message->getIsTopicMessage() && $message->getMessageThreadId() !== null;
    }

    protected function handle(Message &$message): bool
    {
        try {
            $chat_id = (string) $message->getChat()->getId();
            $ticket = ticket::getByGroupThread((int) $chat_id, $message->getMessageThreadId());
            $store = AiRuntimeFactory::createLogStore();
            if ($store->isCompleted((int) $ticket->id)) return false;
            $entry = (new AiTelegramMessageExtractor())->extract(
                $message,
                isAgent($message->getFrom()->getId(), (int) $chat_id) ? 'Агент' : 'Клиент'
            );
            if ($entry !== null) $store->append((int) $ticket->id, $chat_id, $message->getMessageId(), $entry);
        } catch (\Throwable $error) {
            Bot::logThrowable($error, 'Cannot append AI ticket log entry');
        }
        return false;
    }
}
