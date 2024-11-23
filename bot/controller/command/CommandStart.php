<?php

namespace losthost\Oberbot\controller\command;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\note;
use losthost\Oberbot\service\Service;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class CommandStart extends AbstractAuthCommand {
    
    const COMMAND = 'start';
    const PERMIT = self::PERMIT_PRIVATE;

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        if (!$this->args) {
            sendMessage(__('Приветственное сообщение'), 
                    [
                        [['text' => __('️Кнопка Что дальше?'), 'callback_data' => 'verbose']]
                    ]);
        } else {
            $user_id = $message->getFrom()->getId();

            $note = new note(['uuid' => $this->args], true);
            if ($note->isNew() || !$note->canView($user_id)) {
                Service::message('error', 'У вас нет прав на просмотр содержимого этого сообщения.');
            } else {
                Bot::$api->sendMessage(
                        $message->getChat()->getId(), 
                        $note->note);
            }
        }
        
        return true;
    }
    
}
