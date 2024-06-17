<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\private_topic;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\topic_user;
use losthost\telle\Bot;
use losthost\DB\DB;
use losthost\ProxyMessage\Proxy;
use TelegramBot\Api\Types\MessageEntity;
use losthost\ProxyMessage\MessageText;

use function \losthost\Oberbot\message;
use function \losthost\Oberbot\__;
use function losthost\Oberbot\cleanup_message_for_ticket_title;
use function \losthost\Oberbot\newTopicTitle;
use function \losthost\Oberbot\showNewTopicGreating;

/**
 * Обрабатывает сообщения (не команды) в личном чате с ботом
 *
 * @author drweb
 */
class NonCommandPrivateMessage extends AbstractHandlerMessage {
    
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
        
        $private_topic = new private_topic(['user_id' => $message->getFrom()->getId()], true);
        if ($private_topic->isNew()) {
            DB::beginTransaction();
            try {
                $private_topic->ticket_id = $this->newTopic($message);
                $private_topic->write();
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
        $title = $this->makeTitle($message);

        $topic = Bot::$api->createForumTopic(
            $chat_id, 
            $title,
            [7322096, 16766590, 13338331, 9367192, 16749490, 16478047][rand(0,5)]
        );
        
        $proxy = new Proxy(Bot::$api, $this->getUserPrefix($message));
        $proxy->proxy($message, $chat_id, $topic->getMessageThreadId());

        $ticket = new topic(['chat_id' => $chat_id, 'topic_id' => $topic->getMessageThreadId(), 'topic_title' => $title], true);
        if ($ticket->isNew()) {
            $ticket->last_activity = time();
            $ticket->last_admin_activity = 0;
            $ticket->status = topic::STATUS_NEW;
            $ticket->is_urgent = false;
            $ticket->is_task = false;
            $ticket->write();
            $ticket->addIdToTitle();
            $ticket->write();
        } else {
            throw new \Exception('This topic already exists.');
        }

        $ticket_user = new topic_user(['user_id' => $message->getFrom()->getId(), 'topic_number' => $ticket->id], true);
        $ticket_user->isNew() && $ticket_user->write();
        Bot::$api->editForumTopic($chat_id, $ticket->topic_id, $ticket->topic_title);
        
        showNewTopicGreating($ticket);
        return $ticket->id;
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
        $private_topic = new private_topic(['user_id' => $message->getFrom()->getId()]);
        $ticket = new topic(['id' => $private_topic->ticket_id]);
        
        $proxy = new Proxy(Bot::$api, $this->getUserPrefix($message));
        $proxy->proxy($message, Bot::param('chat_for_private_tickets', null), $ticket->topic_id);
        $ticket->last_activity = time();
        $ticket->write();
    }
    
    protected function makeTitle(\TelegramBot\Api\Types\Message &$message) : string {
        if ($message->getText()) {
            return substr(cleanup_message_for_ticket_title($message->getText()), 0, 120);
        } elseif ($message->getCaption()) {
            return substr(cleanup_message_for_ticket_title($message->getCaption()), 0, 120);
        } else {
            return __('Новое обращение');
        }
    }
}
