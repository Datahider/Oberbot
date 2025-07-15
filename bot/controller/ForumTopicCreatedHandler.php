<?php

namespace losthost\Oberbot\controller;

use losthost\Oberbot\data\ticket;
use losthost\telle\abst\AbstractHandlerMessage;
use losthost\telle\Bot;
use losthost\Oberbot\background\CloseIncompleteTicket;

class ForumTopicCreatedHandler extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getForumTopicCreated()) {
            return true;
        }
        return false;
    }
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageId();
        $title = $message->getForumTopicCreated()->getName();
        $creator_id = $message->getFrom()->getId();
        
        if ($creator_id == Bot::param('bot_userid', null)) {
            return true; // бот создал и всё сделал сам
        }
        
        $ticket = ticket::create($group_id, $thread_id, $title, $creator_id);
        
        if ($message->getFrom()->isBot()) {
            $ticket->accept();
            return true; // Другой бот создал топик и должен сам туда всё написать
        }
        
        Bot::runAt(
                date_create(Bot::param("wait_for_first_message", "+10 min")),
                CloseIncompleteTicket::class,
                $ticket->id,
                true
        );
        
        return true;
    }

}
