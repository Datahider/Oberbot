<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\chat_user;
use losthost\telle\Bot;
use losthost\Oberbot\controller\action\ActionCreateTicket;

use function \losthost\Oberbot\__;
use function \losthost\Oberbot\ticketMention;
use function \losthost\Oberbot\mentionById;

class ForwardedMessageToGeneral extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        if ($message->getMessageThreadId()) {
            return false;
        }
        
        if ($message->getForwardOrigin()) {
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $origin = $message->getForwardOrigin();
        
        if ($origin->getType() == 'user') {
            $originator_id = $origin->getSenderUser()->getId();
            $text = 'Создана новая заявка: %ticket_mention%';
        } else {
            $originator_id = $message->getFrom()->getId();
            $text = "Создана новая заявка: %ticket_mention%\n\nПользователем по заявке назначен %mention%, т.к. отправитель оригинала скрыл свои данные.";
        }
        
        $title = 'Новая заявка';
        if ($message->getText()) {
            $title = $message->getText();
        } elseif ($message->getCaption()) {
            $title = $message->getCaption();
        }

        $ticket = ActionCreateTicket::do(Bot::$chat->id, Bot::$chat->id, $originator_id, $title, $message->getMessageId());

        Bot::$api->sendMessage(
            Bot::$chat->id,
            __($text, ['ticket_mention' => ticketMention($ticket), 'mention' => mentionById($originator_id)]),
            'html', 
            false, 
            $message->getMessageId()
        );
        return true;
        
    }
}
