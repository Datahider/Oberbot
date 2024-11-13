<?php

namespace losthost\Oberbot\controller\callback;

use losthost\Oberbot\data\note;
use losthost\Oberbot\service\Service;

class CallbackHid extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = "/^hid_(.*)?/";
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER; // нужно чтобы сам отправитель мог смотреть
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        $user_id = $callback_query->getFrom()->getId();
        $chat_id = $callback_query->getMessage()->getChat()->getId();
        
        $note = new note(['uuid' => $this->matches[1]]);
        if ($note->canView($user_id)) {
            return $note->note;
        }
        return Service::__("Не разрешено.");
    }
}
