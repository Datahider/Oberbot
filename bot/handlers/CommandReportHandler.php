<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\OberbotModel\builders\TimeReport;
use losthost\telle\Bot;
use losthost\BotView\BotView;

class CommandReportHandler extends AbstractHandlerMessage {
    
    protected $parameter;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {

        $text = $message->getText();
        if (!$text) {
            return false;
        }

        $m = [];
        if (preg_match("/^\/[Rr][Ee][Pp][Oo][Rr][Tt]\s*(.*)$/", $text, $m)) {
            $this->parameter = strtolower($m[1]);
            if (!$this->parameter) {
                $this->parameter = 'this month';
            }
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        $report = new TimeReport();
        $params = $this->reportParams();
        $result = $report->build($params);
        
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        $view->show('cmd_report', null, ['report' => $result, 'params' => $params]);
        return true;
    }
    
    protected function reportParams() {
        
        $params = [ 'project' => Bot::$chat->id ];
        
        switch ($this->parameter) {
            case 'this month':
                $params['period_start'] = date_create_immutable('first day of')->format("Y-m-d");
                $params['period_end'] = date_create_immutable('first day of next month')->format("Y-m-d");
                break;
            case 'last month':
                $params['period_start'] = date_create_immutable('first day of last month')->format("Y-m-d");
                $params['period_end'] = date_create_immutable('first day of')->format("Y-m-d");
                break;
            default:
                $m = [];
                if (preg_match("/^(\S+)\s*(\S*)$/", $this->parameter, $m)) {
                    if ($m[2]) {
                        $params['period_start'] = date_create_immutable($m[1])->format("Y-m-d");
                        $params['period_end'] = date_create_immutable($m[2])->format("Y-m-d");
                    } else {
                        $params['period_start'] = date_create_immutable($m[1])->format("Y-m-d");
                        $params['period_end'] = date_create_immutable($m[1])->add(date_interval_create_from_date_string("+24 hours"))->format("Y-m-d");
                    }
                }
        }
        
        return $params;
    }
}
