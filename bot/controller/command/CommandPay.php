<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\action\ActionInvoice;
use losthost\Oberbot\controller\command\AbstractAuthCommand;

use function \losthost\Oberbot\getMentionedIds;

class CommandPay extends AbstractAuthCommand {
    
    const COMMAND = 'pay';
    const PERMIT = self::PERMIT_PRIVATE;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        if (!$this->args) {
            $user_ids = [$this->user_id];
            $this->sendBill($user_ids);
        } else {
            $user_ids = getMentionedIds($message);
            $this->sendBill($user_ids);
        }
        
        return true;
    }
    
    protected function sendBill(array $user_ids) {
        
        ActionInvoice::do(ActionInvoice::PERIOD_1_MONTH, count($user_ids));
        
    }
}
