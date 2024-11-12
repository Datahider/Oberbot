<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;
use losthost\telle\Bot;
use losthost\Oberbot\background\RemindTicket;

class CommandWait extends AbstractAuthCommand {
    
    const COMMAND = 'wait';
    const PERMIT = self::PERMIT_AGENT;
    
    const DATE_TOKENS = [
        'месяца' => 'M',
        'месяцев' => 'M',
        'месяц' => 'M',
        'мес' => 'M',
        'дня' => 'D',
        'дней' => 'D',
        'день' => 'D',
        'д' => 'D',
    ];

    const TIME_TOKENS = [
        'ч' => 'H',
        'час' => 'H',
        'часа' => 'H',
        'часов' => 'H',
        'мин' => 'M',
        'минут' => 'M',
        'минута' => 'M',
        'минуты' => 'M',
        'сек' => 'S',
        'секунда' => 'S',
        'секунды' => 'S',
        'секунд' => 'S',
        'с' => 'S'
    ];
    
    protected int $group_id;
    protected int $thread_id;
    protected int $user_id;

    protected ticket $ticket;


    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $this->group_id = $message->getChat()->getId();
        $this->thread_id = $message->getMessageThreadId();
        $this->user_id = $message->getFrom()->getId();
        $this->ticket = ticket::getByGroupThread($this->group_id, $this->thread_id);
        
        if ($this->thread_id > 1) {
            if ($this->args) {
                Service::message('info', $this->processArguments(), null, $this->thread_id);
            } else {
                $this->ticket->touchAdmin();
                Service::message('info', $this->ticket->entityName(1, true). Service::__(' перемещена в конец очереди.'), null, $this->thread_id);
            }
        } else {
            Service::message('warning', 'Эта команда предназначена для использования только внутри заявки.');
        }
        
        return true;
    }
    
    protected function toIntervalString($arguments) {

        $matches = [];

        preg_match_all("/(\d+)\s*([^\d\s]+)/", $arguments, $matches, PREG_SET_ORDER);

        $date = 'P'; $time = '';
        
        foreach ($matches as $group) {
            if (isset(self::DATE_TOKENS[$group[2]])) {
                $date .= $group[1]. self::DATE_TOKENS[$group[2]];
            } elseif (isset(self::TIME_TOKENS[$group[2]])) {
                $time .= $group[1]. self::TIME_TOKENS[$group[2]];
            }
        }
        
        
        if ($time) {
            $result = "${date}T$time";
        } else {
            $result = $date;
        }
        
        return $result;
    }
    
    protected function processArguments() {
        
        $matches = [];
        if (preg_match("/^#(\d+)$/", $this->args, $matches)) {
            // Задан номер тикета
            $depending_topic = ticket::getById($matches[1]);
            $entity = $depending_topic->entityName(2);
            
            $this->ticket->wait_for = $matches[1];
            $this->ticket->write();
            
            return Service::__($this->ticket->entityName(1, true)). Service::__(" отложена до решения "). "$entity $this->args";
        } elseif (preg_match("/^\d*$/", $this->args)) {
            // Заданы только цифры - считаем, что это минуты
            $interval = new \DateInterval("PT{$this->args}M");
            $now = \date_create_immutable();
            $till = $now->add($interval);
            $this->ticket->wait_till = $till->format('Y-m-d H:i:s');
            $this->ticket->write();
            
            Bot::runAt($till, RemindTicket::class, "$this->user_id {$this->ticket->id}");
            return Service::__($this->ticket->entityName(1, true)). Service::__(" отложена до "). $till->format('d-m-Y H:i');
        } else {
            // Задано что-то, надо попробовать преобразовать это в интервал или дату
            $till = \date_create_immutable($this->args);
            if ($till === false) {
                $now = \date_create_immutable();
                $till = $now->add(new \DateInterval($this->toIntervalString($this->args)));
            }

            $this->ticket->wait_till = $till->format('Y-m-d H:i:s');
            $this->ticket->write();

            Bot::runAt($till, RemindTicket::class, "$this->user_id {$this->ticket->id}");
            return Service::__($this->ticket->entityName(1, true)). Service::__(" отложена до "). $till->format('d-m-Y H:i');
        }
        
        throw new Exception("Ошибка аргументов команды");
    }
    
}
