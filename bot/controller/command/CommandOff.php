<?php

namespace losthost\Oberbot\controller\command;

use losthost\telle\Bot;
use losthost\Oberbot\data\ticket;
use losthost\Oberbot\service\Service;
use losthost\BotView\BotView;
use losthost\templateHelper\Template;

class CommandOff extends AbstractAuthCommand {
    
    const COMMAND = 'off';
    const PERMIT = self::PERMIT_AGENT;
    
    protected int $group_id;
    protected ?int $thread_id;
    protected int $user_id;
    protected int $message_id;


    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {

        if (!$message->getReplyToMessage()) {
            Service::message('warning', 'Вы не указали цитируемое сообщение.', null, $message->getMessageThreadId());
            return true;
        }
        
        $this->group_id = $message->getReplyToMessage()->getChat()->getId();
        $this->thread_id = $message->getReplyToMessage()->getMessageThreadId();
        $this->user_id = $message->getReplyToMessage()->getFrom()->getId();
        $this->message_id = $message->getReplyToMessage()->getMessageId();
        
        $user_banned = $this->banUser();
        
        $quote = null;
        if ($message->getQuote()) {
            $quote = $message->getQuote()->getText();
        }
        $text = $message->getReplyToMessage()->getText();

        if (!$text) {
            $text = $message->getReplyToMessage()->getCaption();
        }
        
        if (!$text) {
            $text = Service::__('Новая заявка из сообщения');
        }
        
        if (!$quote) {
            $quote = substr($text, 0, 50);
        }
        
        $ticket_created = $this->createTicket($quote, $text);
        
        if ($user_banned) {
            $tpl = new Template('controllerOff.php', Service::getUserDataById($this->user_id)->language_code);
            $tpl->assign('user_banned', $user_banned);
            $tpl->assign('ticket_created', $ticket_created);

            Bot::$api->sendMessage($this->group_id, $tpl->process(), 'html', false, $this->message_id, null, false, $this->thread_id);
        }
        
        return true;
    }
    
    protected function banUser() {

        $ban_time = 600;
        if (strlen($this->args) > 0) {
            $ban_time = 60 * $this->args;
        }
        
        if ($ban_time) {
            Bot::$api->restrictChatMember($this->group_id, $this->user_id, \time() + $ban_time);
            return $ban_time / 60;
        }
        return false;
    }
    
    protected function createTicket(?string $subject, ?string $text) {
        
        if (!$subject || !$text) {
            return false;
        }
        
        $forum_topic = Bot::$api->createForumTopic($this->group_id, $subject, Service::getRandomTopicIconColor());
        $new_thread = $forum_topic->getMessageThreadId();
        Bot::$api->forwardMessage($this->group_id, $this->group_id, $this->message_id, false, false, $new_thread);
        Service::message('none', Service::__('Исходное сообщение: '. Service::messageLink($this->group_id, $this->thread_id, $this->message_id)), null, $new_thread);
        
        $new_ticket = ticket::create($this->group_id, $new_thread, $subject, $this->user_id);
        $new_ticket->linkCustomer($this->user_id);
        $new_ticket->accept();
        
        return $new_ticket;
    }
}
