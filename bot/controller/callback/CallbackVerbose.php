<?php

namespace losthost\Oberbot\controller\callback;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use TelegramBot\Api\Types\InputMedia\ArrayOfInputMedia;

use function losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class CallbackVerbose extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^verbose(.*)$/";
    const PERMIT = self::PERMIT_PRIVATE;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        switch ($this->matches[1]) {
            case '':
                sendMessage(__('Сообщение по кнопке Дальше'), [
                    [['text' => __('Как создать группу'), 'callback_data' => 'verbose_how_to_create_group']]
                ]);
                break;
            case '_how_to_create_group':
                $media1 = new InputMediaPhoto();
                $media1->setCaption(__('Инструкции как создать группу'));
                $media1->setMedia('https://storage.losthost.online/Oberbot/img/create_group_1.png');
                $media1->setType('photo');
                $media2 = new InputMediaPhoto();
                $media2->setMedia('https://storage.losthost.online/Oberbot/img/create_group_2.png');
                $media2->setType('photo');
                $media3 = new InputMediaPhoto();
                $media3->setMedia('https://storage.losthost.online/Oberbot/img/create_group_3.png');
                $media3->setType('photo');
                
                $media = new ArrayOfInputMedia([$media1, $media2, $media3]);
                
                Bot::$api->sendMediaGroup(Bot::$chat->id, $media);
                
                break;
            default: 
                return 'ПОКА НЕ РЕАЛИЗОВАНО.';
        }
        
        return true;
    }

}
