<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\view\TicketQueue;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\data\session;

class CommandQueue extends AbstractAuthCommand {
    
    const COMMAND = 'queue';
    const DESCRIPTION = [
        'default' => 'Очередь заявок (активный список)',
    ];
    const PERMIT = self::PERMIT_PRIVATE;

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        $session = new session(['user_id' => $this->user_id, 'chat_id' => $this->chat_id], true);

        $queue = TicketQueue::getQueue($this->user_id, $session->working_group, null);
        
        $view = new BotView(Bot::$api, $this->chat_id, Bot::$language_code);
        $view->show('controllerCommandQueue', null, ['queue' => $queue, 'list' => $session->working_group]);
        
        return true;
    }
    
    static protected function permit(): int {
        return self::PERMIT;
    }

    static public function description(): array {
        return self::DESCRIPTION;
    }
    
}
