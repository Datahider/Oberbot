<?php
namespace losthost\Oberbot\controller\command;

use losthost\Oberbot\data\support_chat;
use losthost\telle\Bot;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\mentionById;

class CommandReserve extends AbstractAuthCommand {
    
    const COMMAND = 'reserve';
    const PERMIT = self::PERMIT_ADMIN;

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $allowed_user_ids = explode(' ', Bot::param('allow_group_reserve', ''));
        if (array_search(Bot::$user->id, $allowed_user_ids) === false) {
            throw new \Exception(__('%mention%, you are not allowed to run this command.', ['mention' => mentionById(Bot::$user->id)]));
        }
        
        $support_chat = new support_chat(['id' => $this->chat_id], true);
        if (!$support_chat->isNew()) {
            $this->reportExists($support_chat);
        } else {
            $support_chat->invite_link = Bot::$api->exportChatInviteLink($this->chat_id);
            $support_chat->reserved_message_id = $this->reportSuccess();
            $support_chat->write();
            
            try {
                Bot::$api->call('editGeneralForumTopic', [
                    'chat_id' => Bot::$chat->id,
                    'name' => __('Общий чат')
                ]);
            } catch (\Exception $ex) {
                Bot::logException($ex);
            }
        }
        
        Bot::$api->deleteMessage($this->chat_id, $message->getMessageId());
        return true;
    }
    
    protected function reportExists(support_chat $support_chat) {
        if ($support_chat->chat_id) {
            $message_id = sendMessage(
                    __('Эта группа уже используется для техподдержки чата: https://t.me/c/%subid%/1', 
                            ['subid' => str_replace('-100', '', $support_chat->chat_id)]));
        } else {
            $message_id = sendMessage(__('Эта группа уже зарезервирована для техподдержки'));
        }
        Bot::runAt(date_create('+2 sec'), \losthost\Oberbot\background\DeleteMessage::class);
    }
    
    protected function reportSuccess() {
    
        return sendMessage(__('Чат зарезервирован для технической поддержки'));
        
    }
}
