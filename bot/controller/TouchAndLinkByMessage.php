<?php

namespace losthost\Oberbot\controller;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\ticket;

use function \losthost\Oberbot\isAgent;
use function \losthost\Oberbot\getMentionedIds;

/**
 * Обновляет время доступа к тикету, а так же привязывает упомянутых пользователей
 * и пользователя отправившего сообщение к тикету. Сделано в процедуре check
 * чтобы обратить внимание на то, что обработка продолжается дальше
 */
class TouchAndLinkByMessage extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $topic_id = $message->getMessageThreadId();
        $group_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        
        $ticket = ticket::getByGroupThread($group_id, $topic_id);

        if (isAgent($user_id, $group_id)) {
            $ticket->touchAdmin()->linkAgent($user_id);
        } else {
            $ticket->touchUser()->linkCustomer($user_id);
        }

        foreach (getMentionedIds($message) as $mentioned_id) {
            if (isAgent($mentioned_id, $group_id)) {
                $ticket->linkAgent($mentioned_id);
            } else {
                $ticket->linkCustomer($mentioned_id);
            }
        }
        
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        return false;
    }
}
