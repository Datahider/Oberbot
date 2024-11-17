<?php

namespace losthost\Oberbot\controller\callback;

use losthost\telle\Bot;
use losthost\BotView\BotView;

use function losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class CallbackVerbose extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^verbose(.*)$/";
    const PERMIT = self::PERMIT_PRIVATE;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $callback_query->getFrom()->getId();
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        switch ($this->matches[1]) {
            case '_cheef':
                sendMessage(__('Инструкции для руководителя'), [
                    [['text' => __("Возможности"), 'callback_data' => 'verbose_cheef_features']],
                    [['text' => __("Стоимость"), 'callback_data' => 'verbose_cheef_price']],
                    [['text' => __("Что дальше"), 'callback_data' => 'verbose_cheef_next']],
                ]);
                break;
            case '_cheef_features':
                sendMessage(__('Инструкции для руководителя - возможности'), [
                    [['text' => __("Пример отчета"), 'callback_data' => 'verbose_report_example']],
                ]);
                break;
            case '_cheef_price':
                sendMessage(__('Инструкции для руководителя - стоимость'));
                break;
            case '_cheef_next':
                sendMessage(__('Инструкции для руководителя - что дальше'));
                break;
            case '_techno':
                sendMessage(__('Инструкции технического специалиста'), [
                    [['text' => __("Возможности"), 'callback_data' => 'verbose_techno_features']],
                    [['text' => __("Стоимость"), 'callback_data' => 'verbose_techno_price']],
                    [['text' => __("Что дальше"), 'callback_data' => 'verbose_techno_next']],
                ]);
                break;
            case '_service_company':
                sendMessage(__('Инструкции для сервисной компании'), [
                    [['text' => __("Возможности"), 'callback_data' => 'verbose_service_company_features']],
                    [['text' => __("Стоимость"), 'callback_data' => 'verbose_service_company_price']],
                    [['text' => __("Что дальше"), 'callback_data' => 'verbose_service_company_next']],
                ]);
                break;
            case '':
                $view->show('controllerCallbackVerbose', 'ctrlkbdCallbackVerbose', [], $callback_query->getMessage()->getMessageId());
                break;
            default: 
                return 'ПОКА НЕ РЕАЛИЗОВАНО.';
        }
        
        return true;
    }

}
