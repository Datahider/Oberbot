<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\data\topic_user;
use losthost\Oberbot\data\private_topic;
use losthost\telle\Bot;
use losthost\ProxyMessage\Proxy;
use TelegramBot\Api\Types\MessageEntity;
use losthost\ProxyMessage\MessageText;

use function \losthost\Oberbot\isAgent;
use function losthost\Oberbot\getMentionedIds;

/**
 * Обработка сообщений агентов в заявке
 *
 * @author drweb
 */
class NonCommandAgentMessage extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getFrom()->getId() === $message->getChat()->getId()) {
            return false;
        } 
        
        if (!$message->getText()) {
            return false;
        }

        if (isAgent($message->getFrom()->getId(), $message->getChat()->getId())) {
            return true;
        }

        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $this->processMentions($message);
        $this->processPrivateTicket($message);
        
        return true;
    }

    protected function processPrivateTicket(\TelegramBot\Api\Types\Message &$message) {

        $ticket = new topic(['topic_id' => $message->getMessageThreadId(), 'chat_id' => $message->getChat()->getId()]);
        $private_topic = new private_topic(['ticket_id' => $ticket->id]);
        
        $proxy = new Proxy(Bot::$api, $this->getAgentPrefix($message));
        $proxy->proxy($message, $private_topic->user_id);
    }
    
    protected function processMentions(\TelegramBot\Api\Types\Message &$message) {
        
        $topic = new topic(['topic_id' => $message->getMessageThreadId(), 'chat_id' => $message->getChat()->getId()]);
        
        $ids = getMentionedIds($message);
        foreach ($ids as $id) {
            $ticket_user = new topic_user(['topic_number' => $topic->$topic->id, 'user_id' => $id], true);
            $ticket_user->isNew() && $ticket_user->write();
        }
    }
    
    protected function getAgentPrefix(\TelegramBot\Api\Types\Message &$message) {
        $agent_name = 'Оператор '. $message->getFrom()->getFirstName();

        $italic = new MessageEntity();
        $italic->setType('italic');
        $italic->setOffset(0);
        $italic->setLength(mb_strlen($agent_name));
        
        $underline = new MessageEntity();
        $underline->setType('underline');
        $underline->setOffset(0);
        $underline->setLength(mb_strlen($agent_name));
        
        return new MessageText("$agent_name\n", [$italic, $underline]);
    }
    
}
