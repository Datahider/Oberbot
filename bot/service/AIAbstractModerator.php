<?php

namespace losthost\Oberbot\service;

use losthost\SimpleAI\SimpleAIAgent;

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
        
    }
    
    /**
     * Обрабатывает значение полученное от LLM и возвращает 
     * значение для возврата из функции check
     */
    abstract protected function checkResult(string $result) : bool|array; 
                                                            
    public function check(string $query) : bool|array {
        
        global $prompts, $deepseek_api_key;
        
        if ($this->subject) {
            $query = <<<FIN
                    Заявка: $this->subject
                    $query
                    FIN;
        }

        $agent = SimpleAIAgent::build($deepseek_api_key)
                ->setAgentName(static::AGENT_NAME)
                ->setUserId($this->user_id)
                ->setPrompt($prompts[static::AGENT_NAME]);
        
        $retry_count = 2;
        return $this->checkResult($agent->ask($query, fn($e) => $this->retryOnTimeout($e, $agent, $retry_count)));
    }
    
    protected function retryOnTimeout(\Throwable $e, SimpleAIAgent $agent, $retry_count) {
        $error_text = $e->getMessage();
        if (preg_match("/^cURL error 28\: Operation timed out/", $error_text)) {
            if ($retry_count <=0) {
                throw "OK"; // Возвращаем ок, если не удалось исправить ошибку
            }
            $retry_count--;

            error_log("Retrying...");
            $agent->setTimeout(10);
            return $agent->ask(null, fn($e) => retryOnTimeout($e, $agent, $retry_count));
        } else {
            throw "OK"; // Возвращаем ок, если это не таймаут, а другая ошибка
        }
    }
}
