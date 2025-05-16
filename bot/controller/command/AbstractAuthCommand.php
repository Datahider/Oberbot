<?php

namespace losthost\Oberbot\controller\command;

use losthost\telle\abst\AbstractHandlerCommand;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\chat;
use TelegramBot\Api\Types\BotCommand;

use function \losthost\Oberbot\isAgent;
use function \losthost\Oberbot\message;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\isChatAdministrator;
use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\mentionById;

abstract class AbstractAuthCommand extends AbstractHandlerCommand {
    
    const PERMIT_NONE = 0;
    const PERMIT_PRIVATE = 1;
    const PERMIT_ADMIN = 2;
    const PERMIT_AGENT = 4;
    const PERMIT_USER = 8;
    
    const PERMIT = self::PERMIT_NONE;

    protected int $current_permit;
    
    protected int $chat_id;
    protected ?int $thread_id;
    protected int $user_id;

    abstract static protected function permit() : int; // Возвращает разрешения команды, например self::PERMIT_PRIVATE | self::PERMIT_AGENT
    abstract static public function description() : array; // Возвращает массив описаний команды. Как минимум один элемент 'default'
    
    public function handleUpdate(\TelegramBot\Api\BaseType &$data): bool {
        
        $this->user_id = $data->getFrom()->getId();
        $this->chat_id = $data->getChat()->getId();
        $this->thread_id = $data->getIsTopicMessage() ? $data->getMessageThreadId() : 1;

        $this->deleteMessageIfNeeded($data);
        
        try {
            if ($this->user_id == $this->chat_id) {
                if (static::permit() & static::PERMIT_PRIVATE) {
                    $this->current_permit = static::PERMIT_PRIVATE;
                    return parent::handleUpdate($data);
                }
            }

            if ( isAgent($this->user_id, $this->chat_id) ) {
                if (static::permit() & static::PERMIT_AGENT) {
                    $this->current_permit = static::PERMIT_AGENT;
                    return parent::handleUpdate($data);
                }
            } else {
                if (static::permit() & static::PERMIT_USER) {
                    $this->current_permit = static::PERMIT_USER;
                    return parent::handleUpdate($data);
                }
            }

            if (isChatAdministrator($this->user_id, $this->chat_id)) {
                if (static::permit() & static::PERMIT_ADMIN) {
                    $this->current_permit = static::PERMIT_ADMIN;
                    return parent::handleUpdate($data);
                }
            }

            
            throw new \Exception('%mention%, you are not allowed to run this command.');
            
        } catch (\Exception $ex) {
            sendMessage(__($ex->getMessage(), ['mention' => mentionById(Bot::$user->id)]), null, null, $this->thread_id);
            Bot::logException($ex);
        }
        
        return true;
    }
    
    protected function deleteMessageIfNeeded($data) {
        $chat = new chat(['id' => $data->getChat()->getId()], true);
        
        if ($chat->isNew()) {
            return;
        }
        
        if (!$chat->delete_commands) {
            return;
        }
        
        try {
            Bot::$api->deleteMessage($data->getChat()->getId(), $data->getMessageId());
        } catch (\Exception $ex) {
            Bot::logException($ex);
        }

    }
    
    protected function showHelp() {
        sendMessage(__(__('Помощь по команде /%command%', ['command' => static::COMMAND])), CommandHelp::getSupportKeyboardArray(Bot::$chat->id), null, $this->thread_id);
    }
    
    public static function getBotCommand(string $scope = 'default') {
        switch ($scope) {
            default:
                $command = new BotCommand();
                $command->setCommand(static::COMMAND);
                $command->setDescription(static::description()['default']);
        }
        return $command;
    }
}
