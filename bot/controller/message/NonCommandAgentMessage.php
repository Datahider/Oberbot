<?php

namespace losthost\Oberbot\controller\message;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\topic_admin;
use losthost\Oberbot\data\topic_user;
use losthost\Oberbot\data\private_topic;
use losthost\telle\Bot;
use losthost\ProxyMessage\Proxy;
use TelegramBot\Api\Types\MessageEntity;
use losthost\ProxyMessage\MessageText;
use losthost\Oberbot\data\ticket;

use function \losthost\Oberbot\isAgent;
use function losthost\Oberbot\getMentionedIds;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

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
        
        if ($message->getChat()->getId() != Bot::param('chat_for_private_tickets', null)) {
            return false;
        }
        
        if ($message->getMessageThreadId() > 1 && isAgent($message->getFrom()->getId(), $message->getChat()->getId())) {
            return true;
        }

        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $this->processPrivateTicket($message);
        
        return true;
    }

    protected function processPrivateTicket(\TelegramBot\Api\Types\Message &$message) {

        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        
        $ticket = ticket::getByGroupThread($group_id, $thread_id);
        $private_topic = new private_topic(['ticket_id' => $ticket->id], true);
        if ($private_topic->isNew()) {
            sendMessage(__('К этому тикету не привязая чат пользователя'), null, $group_id, $thread_id);
            return;
        }
        
        $proxy = new Proxy(Bot::$api, $this->getAgentPrefix($message));
        $proxy->proxy($message, $private_topic->user_id);
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
