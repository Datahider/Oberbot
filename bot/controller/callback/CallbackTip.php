<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\user_meta;

class CallbackTip extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^(.+Tip)$/";
    const PERMIT = self::PERMIT_USER | self::PERMIT_AGENT;
    
    protected int $group_id;
    protected int $thread_id;
    protected int $user_id;
    protected ticket $ticket;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $this->group_id = $callback_query->getMessage()->getChat()->getId();
        $this->thread_id = $callback_query->getMessage()->getMessageThreadId();
        $this->user_id = $callback_query->getFrom()->getId();
        
        $this->ticket = ticket::getByGroupThread($this->group_id, $this->thread_id);
        
        switch ($this->matches[1]) {
            case 'TicketCreatingTip':
                return $this->TicketCreatingTip();
        }
    }
    
    protected function TicketCreatingTip() {
        user_meta::set($this->user_id, 'TicketCreatingTip', 'off');
        return Service::__('Больше не буду показывать это сообщение, когда вы создаёте заявку.');
    }
}
