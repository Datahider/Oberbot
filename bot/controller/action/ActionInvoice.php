<?php

namespace losthost\Oberbot\controller\action;

use losthost\telle\Bot;

use function \losthost\Oberbot\__;

class ActionInvoice {
    
    const PERIOD_1_MONTH = '1month';
    const PERIOD_3_MONTHS = '3months';
    const PERIOD_6_MONTHS = '6months';
    const PERIOD_12_MONTHS = '12months';
    
    protected int $chat_id;
    protected int $thread_id;

    public function __construct(int $chat_id, int $thread_id = null) {
        $this->chat_id = $chat_id;
        $this->thread_id = $thread_id;
    }
    
    public function do(int $period=self::PERIOD_1_MONTH, int $quantity=1) {
        
        Bot::$api->sendInvoice(
                $this->chat_id, 
                __('Название счета %period%', [$period]), 
                __('Описание счета %period%', [$period]), 
                'payload', 
                Bot::param('payment_token', 'secret_payment_token'), 
                null, 
                'RUR',
                [Bot::param('price_'. $period, 0)], 
                false, 
                '');
    }
}
