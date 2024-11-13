<?php

namespace losthost\Oberbot\controller\command;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\note;
use losthost\Oberbot\service\Service;

class CommandStart extends AbstractAuthCommand {
    
    const COMMAND = 'start';
    const PERMIT = self::PERMIT_PRIVATE;

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        if (!$this->args) {
            $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
            $view->show('controllerCommandStart', 'controllerKeyboardCommandStart');
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
