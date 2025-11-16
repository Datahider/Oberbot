<?php

namespace losthost\Oberbot\service;

use losthost\ReflexA\Types\ErrorDescription;

class AIRule_OneTopicOneProblem extends AIAbstractModerator {
    
    const AGENT_NAME = 'onetopic_oneproblem';

    protected function checkResult(string $result): bool|array {
        if ($result == 'OK') {
            return true;
        } else {
            return [
                'text' => $result, 
                'buttons' => [
                    [['text' => '🛑 Заблокировать пользователя на 1 час', 'callback_data' => 'ban_1h']]
                ]
            ];
        }
    }

}
