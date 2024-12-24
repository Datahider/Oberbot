<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\action\ActionInvoice;
use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\DB\DBView;
use losthost\telle\Bot;
use losthost\BotView\BotView;

use function \losthost\Oberbot\getMentionedIds;
use function \losthost\Oberbot\isChatAdministrator;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;

class CommandPay extends AbstractAuthCommand {
    
    const COMMAND = 'pay';
    const PERMIT = self::PERMIT_PRIVATE;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $agent_ids = $this->getMyAgents();
        
        if (!$this->args && empty($agent_ids)) {
            $this->offerSelfPayment();
        } else {
            $user_ids = array_merge(array_diff(getMentionedIds($message), $agent_ids), $agent_ids);
            $this->offerAgentsPayment($user_ids);
        }
        
        return true;
    }
    
    protected function offerSelfPayment() {
        sendMessage(__('Агенты не найдены. Вы хотите оплатить тариф себе?'), $this->getPeriodKeyboard());
    }

    protected function offerAgentsPayment(array $agent_ids) {
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('controllerCommandPay', 'controllerKeyboardCommandPay', ['agent_ids' => $agent_ids]);
    }
    
    protected function getPeriodKeyboard() {
        return [
            [['text' => __(ActionInvoice::PERIOD_1_MONTH), 'callback_data' => 'period_'. ActionInvoice::PERIOD_1_MONTH]],
            [['text' => __(ActionInvoice::PERIOD_3_MONTHS), 'callback_data' => 'period_'. ActionInvoice::PERIOD_3_MONTHS]],
            [['text' => __(ActionInvoice::PERIOD_6_MONTHS), 'callback_data' => 'period_'. ActionInvoice::PERIOD_6_MONTHS]],
            [['text' => __(ActionInvoice::PERIOD_12_MONTHS), 'callback_data' => 'period_'. ActionInvoice::PERIOD_12_MONTHS]],
        ];
    }
    
    protected function getMyAdminGroups() {
        
        $my_chats = new DBView('SELECT chat_id FROM [chat_user] WHERE user_id = ?', [Bot::$user->id]);
        $my_admin_groups = [];
        
        while ($my_chats->next()) {
            if (isChatAdministrator(Bot::$user->id, $my_chats->chat_id)) {
                $my_admin_groups[] = $my_chats->chat_id;
            }
        }
        
        return $my_admin_groups;
    }
    
    protected function getMyAgents() {
        
        $group_ids = $this->getMyAdminGroups();
        
        $my_agents = new DBView(__('SELECT DISTINCT user_id FROM [user_chat_role] WHERE chat_id IN (%groups%) AND role = "agent"', ['groups' => implode(',', $group_ids)])); 
        
        $agent_ids = [];
        
        while ($my_agents->next()) {
            $agent_ids[] = $my_agents->user_id;
        }
        
        return $agent_ids;
    }
    
    protected function sendBill(array $user_ids) {
        
        ActionInvoice::do(ActionInvoice::PERIOD_1_MONTH, $user_ids);
        
    }
}
