<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\service\Service;

class TicketClosing extends DBTracker {
    
    public function track(\losthost\DB\DBEvent $event) {
        
        if (!is_array($event->data) || $event->data['function'] != 'close' || empty($event->fields[0]) ) {
            return;
        }
        
        $ticket = $event->object;
        
        $group_id = $ticket->chat_id;
        $thread_id = $ticket->topic_id;
        $chat = new chat(['id' => $group_id]);
        
        $mention_ids = $ticket->getCustomers();
        if (empty($mention_ids)) {
            $mention_ids[] = $ticket->ticket_creator;
        }
        
        
        $view = new BotView(Bot::$api, $group_id, $chat->language_code);
        $view->show('viewTicketClosing', 'kbdTicketClosing', [
            'mentions' => Service::mentionByIdArray($mention_ids),
            'ticket_time_elapsed' => $ticket->getTimeElapsed()
            ], null, $thread_id);
        
        if ($ticket->getAcceptedMessageId()) {
            $accepting_message = new AcceptingMessage($ticket);
            $accepting_message->show();
        }
        
        try {
            Bot::$api->editForumTopic($group_id, $thread_id, $ticket->topic_title, 5408906741125490282);
        } catch (Exception $ex) {
            Bot::logException($ex);
        }
        
        try {
            Bot::$api->closeForumTopic($group_id, $thread_id);
        } catch (Exception $ex) {
            Bot::logException($ex);
        }
    }
}
