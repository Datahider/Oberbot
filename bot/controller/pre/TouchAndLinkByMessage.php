<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;

use function \losthost\Oberbot\isAgent;
use function \losthost\Oberbot\getMentionedIds;

/**
 * Обновляет время доступа к тикету, а так же привязывает упомянутых пользователей
 * и пользователя отправившего сообщение к тикету. Сделано в процедуре check
 * чтобы обратить внимание на то, что обработка продолжается дальше
 */
class TouchAndLinkByMessage extends AbstractHandlerMessage {
    
    const BLOCK_REGULAR_GROUP = 1;
    const BLOCK_NO_TICKET = 2;
    const BLOCK_SERVICE = 3;
    
    protected int $reason;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        
        $topic_id = $message->getMessageThreadId();
        $group_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();

        if ($user_id == $group_id) {
            return false; // Личное сообщение боту
        } elseif (empty($topic_id)) {
            $this->reason = self::BLOCK_REGULAR_GROUP;
            return true; 
        }
        
        if (!$message->getText() && !$message->getCaption()) {
            return false; // не должны обрабатывать служебные сообщения, 
                          // т.к. они могут содержать упоминания пользователей
                          // но при этом нужны для проверки, возможно это переоткрытие 
                          // или закрытие тикета
        }

        try {
            if ($topic_id > 1 && !$message->getForumTopicCreated()) { // попробуем найти тикет в базе
                $ticket = ticket::getByGroupThread($group_id, $topic_id);
            } else {
                return false; // Это сообщение в общий чат форума
            }
        } catch (Exception $ex) {
            // Не найден тикет, возможно нет в базе
            $this->reason = self::BLOCK_NO_TICKET;
            return true; 
        }
        
        if (isAgent($user_id, $group_id)) {
            $ticket->touchAdmin();
            $ticket->hasAgent($user_id) || $ticket->linkAgent($user_id);
        } else {
            $ticket->touchUser();
            $ticket->hasCustomer($user_id) || $ticket->linkCustomer($user_id);
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
        
        switch ($this->reason) {
            case self::BLOCK_REGULAR_GROUP:
                Bot::logComment('Сообщение в обычной группе. Игнорим.');
                return true; // Как будто уже обработано;
            case self::BLOCK_NO_TICKET:
                $topic_id = $message->getMessageThreadId();
                $group_id = str_replace("-100", "", $message->getChat()->getId());
                Bot::logComment("Не найден тикет для топика https://t.me/c/$group_id/$topic_id");
                return true;
        }
        return false;
    }
}
