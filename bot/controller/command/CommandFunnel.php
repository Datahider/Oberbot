<?php

namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\controller\command\AbstractAuthCommand;
use losthost\Oberbot\data\support_chat;
use losthost\Oberbot\data\funnel_chat; 
use losthost\telle\Bot;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;
use function losthost\Oberbot\getChatOwner;

class CommandFunnel extends AbstractAuthCommand {
    
    const COMMAND = 'funnel';
    const PERMIT = self::PERMIT_ADMIN;
    const DESCRIPTION = [
        'default' => 'Добавление группы как резервной в воронку'
    ];
    
    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
    
        $support = new support_chat(['id' => $this->chat_id], true);
        $funnel = new funnel_chat(['id' => $this->chat_id], true);
        
        if (!$support->isNew()) {
            sendMessage(__('Эта группа уже используется для внутренней поддержки @oberbot'));
            return true;
        }

        if (!$funnel->isNew()) {
            sendMessage(__('Эта группа уже используется воронки'));
            return true;
        }
        
        $funnel->owner_id = getChatOwner(Bot::$chat->id)->getId();
        $funnel->invite_link = Bot::$api->exportChatInviteLink(Bot::$chat->id);
        $funnel->write();
        
        Bot::$api->call('editGeneralForumTopic', [
            'chat_id' => Bot::$chat->id,
            'name' => __('Общий чат')
        ]);
        $message_id = sendMessage(__('Группа будет использоваться для подключения новых пользователей.'));
        sleep(3);
        Bot::$api->deleteMessage(Bot::$chat->id, $message_id);
        
        return true;
    }

    static protected function permit(): int {
        return self::PERMIT;
    }

    static public function description(): array {
        return self::DESCRIPTION;
    }
}
