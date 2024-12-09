<?php

namespace losthost\Oberbot\controller\command;

use losthost\telle\Bot;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\note;
use losthost\telle\abst\AbstractHandlerMessage;


class CommandHid extends AbstractAuthCommand {

    const COMMAND = 'hid';
    const DESCRIPTION = [
        'default' => 'Отправка скрытого сообщения',
        'all_group_chats' => 'Отправка скрытого сообщения'
    ];
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_USER;
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $args = $this->args;
        $group_id = $message->getChat()->getId();
        $thread_id = $message->getMessageThreadId();
        $user_id = $message->getFrom()->getId();
        
        if ($args) {
            Bot::$api->deleteMessage($group_id, $message->getMessageId());
            $mentioned_ids = Service::getMentionedIds($message);
            note::create($args, $group_id, $thread_id, $user_id, $mentioned_ids);
            
        } else {
            Service::message('info', 'Описание команды /hid', null, $thread_id);
        }
        
        return true;
    }

}
