<?php

namespace losthost\Oberbot\controller\action;

use losthost\telle\Bot;
use TelegramBot\Api\Types\Payments\LabeledPrice;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionByIdArray;
use function \losthost\Oberbot\textMentionByIdArray;

class ActionInvoice {
    
    const PERIOD_1_MONTH = '1month';
    const PERIOD_3_MONTHS = '3months';
    const PERIOD_6_MONTHS = '6months';
    const PERIOD_12_MONTHS = '12months';
    
    static public function do(string $period, array $user_ids) {
        
        $quantity = count($user_ids);
        $price = new LabeledPrice();
        $price->setLabel('product price');
        $price->setAmount($quantity * 100 * Bot::param('price_'. $period, 0));
        
        $mentions = textMentionByIdArray($user_ids);
        
        $method = 'sendInvoice';
        $data = [
            'chat_id' => Bot::$chat->id,
            'title' => __('Название счета %period%', ['period' => __($period)]),
            'description' => __('Описание счета %period% %quantity% %users%', ['period' => __($period), 'quantity' => $quantity, 'users' => $mentions]),
            'payload' => 'payload',
            'provider_token' => Bot::param('payment_token', 'secret_payment_token'),
            'currency' => 'RUB',
            'prices' => json_encode([
                ['label' => __('%quantity% агент(а,ов) на %period%', ['period' => __($period), 'quantity' => $quantity]), 'amount' => $quantity * 100 * Bot::param('price_'. $period, 0)]
            ]),
            'photo_url' => 'https://oberdesk.ru/img/bill3.jpg'
        ];
        
        Bot::$api->call($method, $data);
        
    }
}
