<?php

namespace losthost\Oberbot\service;

use losthost\ReflexA\Mind\SimpleAgent;
use losthost\ReflexA\Data\UserAgentData;
use losthost\ReflexA\Types\ErrorDescription;

/**
 * Предназначен для проверки соответствия реплик полььзователей правилам 
 */
abstract class AIAbstractModerator {
    
    const AGENT_NAME = '';
    
    protected string $user_id;
    protected bool $is_useable;
    protected ?string $subject;


    public function __construct(string $topic_id, ?string $subject=null) {

        if (!static::AGENT_NAME) {
            throw new \Exception("Please set AGENT_NAME");
        }
        $this->user_id = $topic_id;
        $this->subject = $subject;
        
        if (!$subject) {
            $user_data = UserAgentData::getByUserAgent($this->user_id, static::AGENT_NAME);
            if ($user_data->isNew()) {
                $this->is_useable = false;
                return;
            }
        }

        $this->is_useable = true;
    }
    
    /**
     * Обрабатывает значение {'message': 'Сообщение об ошибке', 'description': 'Трассировка'}
     * и возвращает значение, которое должна вернуть функция check при ошибке
     */
    abstract protected function error(ErrorDescription $result) : bool|array; 
    
    /**
     * Обрабатывает значение полученное от LLM и возвращает 
     * значение для возврата из функции check
     */
    abstract protected function checkResult(string $result) : bool|array; 
                                                            


    public function isUseable() {
        return $this->is_useable;
    }
    
    public function check(string $query) : bool|array {
        
        if (!$this->is_useable) {
            throw new \Exception("The instance in not useable.");
        }

        
        if ($this->subject) {
            $query = <<<FIN
                    Заявка: $this->subject
                    $query
                    FIN;
        }

        $agent = new SimpleAgent($this->user_id, static::AGENT_NAME);
        
        
        $answer = $agent->query($query);
        
        if ($agent->hasError()) {
            return $this->error($agent->getLastError());
        }
        return $this->checkResult($answer);
    }
}
