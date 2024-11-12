<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\service\Service;

class ChatCreateUpdate extends DBTracker {
    
    protected chat $chat;
    
    public function track(\losthost\DB\DBEvent $event) {
        
        if (!empty($event->data['mute'])) {
            return;
        }
        
        $this->chat = $event->object;
        $processing = $this->chat->process_tickets ? Service::__('Включена') : Service::__('Отключена');
        $lang = $this->chat->language_code ? 'default' : $this->chat->language_code;
        Service::message('info', sprintf(Service::__("Обработка сообщений: <b>%s</b>\nКод языка: <b>%s</b>"), $processing, $lang));
        
    }
}
