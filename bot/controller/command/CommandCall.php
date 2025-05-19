<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\controller\action\ActionCreateTicket;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\ticketMention;
use function \losthost\Oberbot\getMentionedIds;
use function \losthost\Oberbot\mentionByIdArray;

class CommandCall extends AbstractAuthCommand {
    
    const COMMAND = 'call';
    
    protected array $mentioned_ids;


    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        $this->mentioned_ids = getMentionedIds($message);
        
        if ($this->thread_id >= 2) {
            $this->createAndStartConsultSubtask();
        } else {
            $this->createAndStartConsultTask();
        }
        return true;
    }

    static protected function permit(): int {
        return self::PERMIT_AGENT;
    }

    static public function description(): array {
        return [
            'default' => 'Создание (под)задачи с типом Консультация'
        ];
    }
    
    protected function createAndStartConsultSubtask() {
        $old_ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
        
        if (empty($this->mentioned_ids)) {
            $this->mentioned_ids = $old_ticket->getCustomers();
        }
        $mention = mentionByIdArray($this->mentioned_ids);
        $mention4title = $this->getMentions4Title($mention);
        
        $ticket = ActionCreateTicket::do($this->chat_id, $this->chat_id, $this->user_id, __('Консультация %user%', ['user' => $mention4title]), __('Согласованная консультация по задаче %ticket%', ['ticket' => ticketMention($old_ticket)]));
        $ticket->setType(ticket::TYPE_SCHEDULED_CONSULT);
        $old_ticket->waitTask($ticket->id);
        $ticket->timerStart($this->user_id);
    }

    protected function createAndStartConsultTask() {
        if (empty($this->mentioned_ids)) {
            sendMessage(__('Вы не указали пользователя, от которого получен звонок.'));
        } else {
            $mentions = mentionByIdArray($this->mentioned_ids);
            $mentions4title = $this->getMentions4Title($mentions);
            $ticket = ActionCreateTicket::do($this->chat_id, $this->chat_id, $this->user_id, __('Срочная консультация %user%', ['user' => $mentions4title]), __('Срочная консультация пользователя %mention%', ['mention' => $mentions]));
            $ticket->setType(ticket::TYPE_URGENT_CONSULT);
            $ticket->timerStart($this->user_id);
        }
    }
    
    protected function getMentions4Title($mentions) {
        return preg_replace("/\<.*?\>/", '', $mentions);
    }

}
