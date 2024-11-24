<?php

namespace losthost\Oberbot\controller\callback;

use losthost\telle\Bot;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\data\support_chat;
use losthost\Oberbot\service\Service;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\sendMessage;

class CallbackHelp extends AbstractCallback {
    
    const CALLBACK_DATA_PATTERN = '/^(call|go)_help$/';
    const PERMIT = self::PERMIT_AGENT | self::PERMIT_ADMIN;
    
    public function processCallback(\TelegramBot\Api\Types\CallbackQuery &$callback_query): string|bool {
        
        switch ($this->matches[1]) {
            case 'call':
                $this->callHelp($callback_query);
                break;
            case 'go':
                $this->goHelp($callback_query);
                break;
        }
        
        Bot::$api->editMessageReplyMarkup(Bot::$chat->id, $callback_query->getMessage()->getMessageId());
        return true;
    }
    
    protected function callHelp(\TelegramBot\Api\Types\CallbackQuery &$callback_query) {

        $invite_link = Bot::$api->createChatInviteLink(Bot::$chat->id, 'Oberbot Support');
        $developers_chat_id = Bot::param('developers_group_id', null);
        $title = __('Приглашение в группу для поддержки');
        $user_id = $callback_query->getFrom()->getId();
        
        $forum_topic = Bot::$api->createForumTopic(
                $developers_chat_id, 
                $title, 
                Service::getRandomTopicIconColor());
        
        $new_thread = $forum_topic->getMessageThreadId();
        
        $thread_id = $callback_query->getMessage()->getIsTopicMessage() ? $callback_query->getMessage()->getMessageThreadId() : 1;
        $subid = str_replace('-100', '', Bot::$chat->id);
        $message_id = $callback_query->getMessage()->getMessageId();
        
        $message_link = "https://t.me/c/$subid/$thread_id/$message_id";
        
        sendMessage(__('Вас приглашают для поддержки в чат %link%. Исходное сообщение %message_link%', ['link' => $invite_link->getInviteLink(), 'message_link' => $message_link]), null, $developers_chat_id, $new_thread);
        
        $new_ticket = ticket::create($developers_chat_id, $new_thread, $title, $user_id);
        $new_ticket->linkCustomer($user_id);
        $new_ticket->accept();
        $new_ticket->toTicket();
        $new_ticket->setUrgent();

        sendMessage(__('Отправил приглашение нашим специалистам.'));
    }
    
    protected function goHelp(\TelegramBot\Api\Types\CallbackQuery &$callback_query) {
        
        try {
            $invite_link = support_chat::getChatInviteLink(Bot::$chat->id);
            sendMessage(__('Создал чат для поддержки вашей группы: %link%', ['link' => $invite_link]));
        } catch (\Exception $ex) {
            $invite_link = Bot::param('public_support_group', null);
            sendMessage(__('Перейдите в нашу группу технической поддержки %link%', ['link' =>  $invite_link]));
        }
        
    }
}
