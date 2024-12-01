<?php

namespace losthost\Oberbot\controller\message;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\private_topic;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\topic_user;
use losthost\telle\Bot;
use losthost\DB\DB;
use losthost\ProxyMessage\Proxy;
use TelegramBot\Api\Types\MessageEntity;
use losthost\ProxyMessage\MessageText;
use losthost\Oberbot\controller\action\ActionCreateTicket;
use losthost\Oberbot\data\ticket;

use function \losthost\Oberbot\message;
use function \losthost\Oberbot\__;
use function losthost\Oberbot\cleanup_message_for_ticket_title;
use function \losthost\Oberbot\sendMessage;

/**
 * Обрабатывает сообщения (не команды) в личном чате с ботом
 *
 * @author drweb
 */
class NonCommandPrivateMessage extends AbstractHandlerMessage {
    
    protected private_topic $private_topic;
    protected ticket $ticket;


    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        
        if (substr($message->getText(), 0, 1) === '/') {
            return false;
        }

        if ($message->getFrom()->getId() === $message->getChat()->getId()) {
            return true; // Это личное сообщение боту
        }
        
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        if (Bot::param('chat_for_private_tickets', null) == null) {
            message('warning', __('Этот бот не обрабатывает обращения в приватном чате.'));
            return true;
        }
        
        $this->private_topic = new private_topic(['user_id' => $message->getFrom()->getId()], true);
        if ($this->private_topic->isNew()) {
            DB::beginTransaction();
            try {
                $this->private_topic->ticket_id = $this->newTopic($message);
                $this->private_topic->write();
                DB::commit();
            } catch (Exception $exc) {
                Bot::logException($exc);
                DB::rollBack();
                message('error', __('Не удалось создать обращение.'));
            }
        } else {
            $this->oldTopic($message);
        }

        return true;
    }
    
    protected function newTopic(\TelegramBot\Api\Types\Message &$message) : int { // new topic id
        
        $chat_id = Bot::param('chat_for_private_tickets', null);
        $uid = $message->getFrom()->getUsername() ? '@'. $message->getFrom()->getUsername() : $message->getFrom()->getId();
        $title = trim(sprintf('%s | %s %s', $uid, $message->getFrom()->getFirstName(), $message->getFrom()->getLastName()));

        $from_id = $message->getFrom()->getId();
        $message_id = $message->getMessageId();
        
        $this->ticket = ActionCreateTicket::do($from_id, $chat_id, $from_id, $title, [$message_id]);

        $proxy = new Proxy(Bot::$api, $this->getUserPrefix($message));
        $proxy->proxy($message, $chat_id, $this->ticket->topic_id);

        $this->ticket->toTicket();
        $this->ticket->setUrgent();
        
        return $this->ticket->id;
    }

    protected function getUserPrefix(\TelegramBot\Api\Types\Message &$message) {
        $user_name = 'Пользователь '. $message->getFrom()->getFirstName();

        $italic = new MessageEntity();
        $italic->setType('italic');
        $italic->setOffset(0);
        $italic->setLength(mb_strlen($user_name));
        
        $underline = new MessageEntity();
        $underline->setType('underline');
        $underline->setOffset(0);
        $underline->setLength(mb_strlen($user_name));
        
        return new MessageText("$user_name\n", [$italic, $underline]);
    }
    
    protected function oldTopic(\TelegramBot\Api\Types\Message &$message) {
        $this->ticket = ticket::getById($this->private_topic->ticket_id);
        
        $proxy = new Proxy(Bot::$api, $this->getUserPrefix($message));
        $proxy->proxy($message, Bot::param('chat_for_private_tickets', null), $this->ticket->topic_id);
        $this->ticket->touchUser();
        if ($this->ticket->status == ticket::STATUS_CLOSED) {
            $this->ticket->reopen();
        }
    }
    
    protected function makeTitle(\TelegramBot\Api\Types\Message &$message) : string {
        if ($message->getText()) {
            return mb_substr(cleanup_message_for_ticket_title($message->getText()), 0, 120);
        } elseif ($message->getCaption()) {
            return mb_substr(cleanup_message_for_ticket_title($message->getCaption()), 0, 120);
        } else {
            return __('Новое обращение');
        }
    }
}
