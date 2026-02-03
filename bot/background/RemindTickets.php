<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\data\chat;
use losthost\DB\DBList;
use losthost\telle\Bot;
use losthost\BotView\BotView;

class RemindTickets extends AbstractBackgroundProcess {
    
    const FILTER_TYPES = [
        ticket::TYPE_MALFUNCTION,
        ticket::TYPE_MALFUNCTION_FREE,
        ticket::TYPE_MALFUNCTION_MULTIUSER,
        ticket::TYPE_BOT_SUPPORT
    ];
    
    const FILTER_STATUSES = [
        ticket::STATUS_NEW,
        ticket::STATUS_REOPEN,
        ticket::STATUS_USER_ANSWERED
    ];
    
    public function run() {
        
        Bot::$language_code = 'default';
        
        $agent_tickets = $this->getAgentTicketsArray();
        foreach ($agent_tickets as $agent_id => $tickets) {

            $view = new BotView(Bot::$api, $agent_id, Bot::$language_code);
            $view->show('backgroundRemindTickets', null, ['tickets' => $tickets]);
            
        }

    }
    
    protected function getAgentTicketsArray() {
        $agent_tickets = [];
        
        $tickets = $this->getTickets();
        foreach ($tickets as $ticket) {
            $agent_ids = $this->getAgentIds($ticket);
            foreach ($agent_ids as $agent_id) {
                $agent_tickets[$agent_id][] = $ticket;
            }
        }
        return $agent_tickets;
    }
    
    protected function getAgentIds(ticket &$ticket) {
        $agent_ids = $ticket->getAgents();
        if (empty($agent_ids)) {
            $chat = new chat(['id' => $ticket->chat_id]);
            $agent_ids = $chat->getAgentIds();
        }
        return $agent_ids;
    }
    
    protected function getTickets() : array {
        $filter_types = implode(', ', static::FILTER_TYPES);
        $filter_statuses = implode(', ', static::FILTER_STATUSES);
        
        $filter = <<<FIN
                type IN ($filter_types)
                AND status IN ($filter_statuses)
                FIN;
        
        $tickets_list = new DBList(ticket::class, $filter, []);
        return $tickets_list->asArray();
    }
}
