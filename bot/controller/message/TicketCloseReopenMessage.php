<?php

namespace losthost\Oberbot\controller\message;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;
use losthost\telle\Bot;

class TicketCloseReopenMessage extends AbstractHandlerMessage {
    
    protected bool $is_closed;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getForumTopicClosed()) {
            $this->is_closed = true;
            return true;
        } elseif ($message->getForumTopicReopened()) {
            $this->is_closed = false;
            return true;
        }
        
        return false;
    }
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $ticket = ticket::getByGroupThread(
                $message->getChat()->getId(), 
                $message->getMessageThreadId());
        
        if ($this->is_closed && $ticket->status <> ticket::STATUS_CLOSED && ticket->status <> ticket::STATUS_ARCHIVED) {
            $ticket->close();
        } elseif (!$this->is_closed && $ticket->status == ticket::STATUS_CLOSED) {
            $ticket->reopen();
        } elseif (!$this->is_closed && $ticket->status == ticket::STATUS_ARCHIVED) {
            Service::message('warning', 'Заявка находится в архиве. Переоткрытие не возможно.', 'Предупреждение', $ticket->topic_id);
            try {
                Bot::$api->closeForumTopic($ticket->chat_id, $ticket->topic_id);
            } catch (\Exception $ex) {
                Bot::logException($ex);
            }
        }
        
        return true;
    }
}
