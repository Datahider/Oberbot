<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\builders\TimeReport;
use losthost\BotView\BotView;
use losthost\telle\Bot;

class CommandReport extends AbstractAuthCommand {
    
    const COMMAND = 'report';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;

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
        
        switch (strtolower($this->args)) {
            case '':
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
                if (preg_match("/^(\S+)\s*(\S*)$/", $this->args, $m)) {
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
