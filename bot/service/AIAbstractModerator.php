<?php

namespace losthost\Oberbot\service;

use losthost\ReflexA\Mind\UserQuery;
use losthost\DB\DBList;
use losthost\ReflexA\Data\UserData;

/**
 * Предназначен для проверки соответствия реплик полььзователей правилам 
 */
abstract class AIAbstractModerator {
    
    protected string $topic_id;
    protected UserQuery $user_query;
    protected boot $is_useable;
    protected string $subject;


    public function __construct(string $topic_id, ?string $subject=null) {

        $this->topic_id = $topic_id;
        
        if (!$subject) {
            $user_data = new DBList(UserData::class, ['id' => $topic_id]);
            if (!$user_data->next()) {
                $this->is_useable = false;
                return;
            }
        } else {
            $this->subject = $subject;
        }

        $this->user_query = new UserQuery($this->topic_id);
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
    abstract protected function error(\stdClass $result) : bool|string; 
    
    /**
     * Обрабатывает значение полученное от LLM и возвращает 
     * значение для возврата из функции check
     */
    abstract protected function checkResult(string $result) : bool|string; 
                                                            


    public function isUseable() {
        return $this->is_useable;
    }
    
    public function check(string $query) : bool|string {
        
        if (!$this->is_useable) {
            throw new \Exception("The instance in not useable.");
        }
        
        $result = $this->user_query->query($query);
        
        if (!empty($result->error)) {
            return $this->error($result);
        } else {
            return $this->checkResult($result->choices[0]->message->content);
        }
    }
    
}
