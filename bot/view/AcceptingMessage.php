<?php

namespace losthost\Oberbot\view;

use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\chat;

/**
 * Используется для отображения и обновления сообщения о принятии топика в систему Helpdesk
 * Отображает номер, тип, текущий статус заявки, затраченное время, привязанные пользователи и агенты
 *
 * @author drweb
 */
class AcceptingMessage {
    
    protected $ticket;
    
    public function __construct(ticket $ticket) {
        $this->ticket = $ticket;
    }
    
    public function show() {
        $chat = new chat(['id' => $this->ticket->chat_id], true);
        $view = new BotView(Bot::$api, $chat->id, $chat->language_code);
        
        $message_id = $view->show('viewAcceptingMessage', 'kbdAcceptingMessage', ['ticket' => $this->ticket], $this->ticket->getAcceptedMessageId(), $this->ticket->topic_id);
        $this->ticket->setAcceptedMessageId($message_id);
    }
}
