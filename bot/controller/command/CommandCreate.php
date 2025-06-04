<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;

class CommandCreate extends AbstractAuthCommand {
    
    const COMMAND = 'create';
    const DESCRIPTION = [
        'default' => 'Создание тикета в базе данных бота (служебная)',
    ];
    const PERMIT = self::PERMIT_AGENT;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        try {
            ticket::getByGroupThread($this->chat_id, $this->thread_id);
            Service::message('warning', "Заявка связанная с этим топиком уже существует.", null, $this->thread_id);
        } catch (\Exception $ex) {
            if ($this->args) {
                $ticket = ticket::create($this->chat_id, $this->thread_id, $this->args, $this->user_id);
                $ticket->accept();
            } else {
                sendMessage(__("Необходимо задать тему заявки"), null, null, $this->thread_id);
            }
        }
        
        return true;
    }
    
    static protected function permit(): int {
        return self::PERMIT;
    }

    static public function description(): array {
        return self::DESCRIPTION;
    }
    
}
