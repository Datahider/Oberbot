<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\background\RemindTicket;
use losthost\timetracker\Timer;

class CommandWait extends AbstractAuthCommand {
    
    const COMMAND = 'wait';
    const DESCRIPTION = [
        'default' => 'Откладывание задачи на время или до решения другой задачи',
    ];
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
    
    protected ticket $ticket;


    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $this->chat_id = $message->getChat()->getId();
        $this->thread_id = $message->getMessageThreadId();
        $this->user_id = $message->getFrom()->getId();
        $this->ticket = ticket::getByGroupThread($this->chat_id, $this->thread_id);
        
        if ($this->thread_id > 1) {
            if ($this->args) {
                $this->processArguments();
            } else {
                $this->processNoArgs();
            }
            
            if ($this->ticket->isTimerStarted($this->user_id)) {
                $this->ticket->timerStop($this->user_id);
            }
            Service::showNextTicket($this->user_id);
        } else {
            Service::message('warning', 'Эта команда предназначена для использования только внутри заявки.');
        }
        
        return true;
    }
    
    protected function processNoArgs() {

        $this->ticket->touchAdmin($this->user_id);

        $view_params = [
            'ticket' => $this->ticket
        ];
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('controllerCommandWait', null, $view_params, null, $this->ticket->topic_id);
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
            $result = "{$date}T$time";
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
            
            $this->ticket->waitTask($matches[1]);
            
            return;
        } elseif (preg_match("/^\d*$/", $this->args)) {
            // Заданы только цифры - считаем, что это минуты
            $interval = new \DateInterval("PT{$this->args}M");
            $now = \date_create_immutable();
            $till = $now->add($interval);
            $this->ticket->waitTime($till);
            
            Bot::runAt($till, RemindTicket::class, "$this->user_id {$this->ticket->id}");
            return;
        } else {
            // Задано что-то, надо попробовать преобразовать это в интервал или дату
            $till = \date_create_immutable($this->args);
            if ($till === false) {
                $now = \date_create_immutable();
                $interval = new \DateInterval($this->toIntervalString($this->args));
                $till = $now->add($interval);
            } elseif ($till->getTimestamp() < time()) {
                $till = $till->add(new \DateInterval('P1D'));
            }


            $this->ticket->wait_till = $till->format('Y-m-d H:i:s');
            $this->ticket->write();

            Bot::runAt($till, RemindTicket::class, "$this->user_id {$this->ticket->id}");
            return;
        }
        
        throw new Exception("Ошибка аргументов команды");
    }
    
}
