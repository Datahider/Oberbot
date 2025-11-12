<?php

namespace losthost\Oberbot\service;

use losthost\ReflexA\Mind\UserQuery;
use losthost\DB\DBList;
use losthost\ReflexA\Data\UserData;
use losthost\ReflexA\Data\Context;

/**
 * Предназначен для проверки соответствия реплик полььзователей правилам 
 */
abstract class AIAbstractModerator {
    
    const AGENT_NAME = '';
    
    protected string $topic_id;
    protected UserQuery $user_query;
    protected bool $is_useable;
    protected ?string $subject;


    public function __construct(string $topic_id, ?string $subject=null) {

        if (!static::AGENT_NAME) {
            throw new \Exception("Please set AGENT_NAME");
        }
        $this->topic_id = $topic_id;
        $this->subject = $subject;
        
        if (!$subject) {
            $user_data = new DBList(UserData::class, ['id' => $topic_id]);
            if (!$user_data->next()) {
                $this->is_useable = false;
                return;
            }
        }

        $this->user_query = new UniQuery($this->topic_id, static::AGENT_NAME);
        $this->is_useable = true;
    }
    
    /**
     * Задает промпт используемый для анализа последующих запросов в заданном топике
     */
    abstract protected function getPrompt() : string;
    
    /**
     * Обрабатывает значение {'message': 'Сообщение об ошибке', 'description': 'Трассировка'}
     * и возвращает значение, которое должна вернуть функция check при ошибке
     */
    abstract protected function error(\stdClass $result) : bool|array; 
    
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
                    Тема: $this->subject
                    
                    $query
                    FIN;
        }

        $user_message = new Context();
        $user_message->user = $this->topic_id;
        $user_message->role = 'user';
        $user_message->content = $query;
        $user_message->date_time = date_create();
        $user_message->write();
        
        $result = $this->user_query->query($query);
        
        if (!empty($result->error)) {
            return $this->error($result);
        } else {
            $ai_message = new Context();
            $ai_message->user = $this->topic_id;
            $ai_message->role = 'assistant';
            $ai_message->content = $result->choices[0]->message->content;
            $ai_message->date_time = date_create();
            $ai_message->write();
            return $this->checkResult($ai_message->content);
        }
    }
    
}
