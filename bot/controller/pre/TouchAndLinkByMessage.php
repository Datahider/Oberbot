<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\ticket;
use losthost\telle\Bot;
use losthost\Oberbot\service\GroupWizard;
use losthost\Oberbot\controller\command\CommandHid;

use function \losthost\Oberbot\isAgent;
use function \losthost\Oberbot\getMentionedIds;

/**
 * Обновляет время доступа к тикету, а так же привязывает упомянутых пользователей
 * и пользователя отправившего сообщение к тикету. Сделано в процедуре check
 * чтобы обратить внимание на то, что обработка продолжается дальше
 */
class TouchAndLinkByMessage extends AbstractHandlerMessage {
    
    const BLOCK_REGULAR_GROUP = 1;
    const BLOCK_NO_TICKET = 2;
    const BLOCK_SERVICE = 3;
    const BLOCK_GENERAL_TOPIC = 4;
    
    protected int $reason;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        
        $topic_id = $message->getMessageThreadId();
        $group_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        $is_forum = $message->getChat()->getIsForum();
        
        if ($user_id == $group_id) {
            return false; // Личное сообщение боту
        } elseif (!$is_forum) {
            $this->reason = self::BLOCK_REGULAR_GROUP;
            return true; 
        } elseif ($is_forum && !$topic_id) {
            $this->reason = self::BLOCK_GENERAL_TOPIC; // для всего кроме команды /hid
            return true;
        }
        
        if (!$message->getText() 
                && !$message->getAnimation() 
                && !$message->getAudio() 
                && !$message->getDocument() 
                && !$message->getPhoto() 
                && !$message->getSticker() 
                && !$message->getStory() 
                && !$message->getVideo() 
                && !$message->getVideoNote() 
                && !$message->getVoice()) {
            return false; // не должны обрабатывать служебные сообщения, 
                          // т.к. они могут содержать упоминания пользователей
                          // но при этом нужны для проверки, возможно это переоткрытие 
                          // или закрытие тикета
        }

        try {
            if ($topic_id > 1 && !$message->getForumTopicCreated()) { // попробуем найти тикет в базе
                $ticket = ticket::getByGroupThread($group_id, $topic_id);
            } else {
                return false; // Это сообщение в общий чат форума
            }
        } catch (Exception $ex) {
            // Не найден тикет, возможно нет в базе
            $this->reason = self::BLOCK_NO_TICKET;
            return true; 
        }
        
        if ($ticket->hasAgent($user_id)) {
            $ticket->touchAdmin($user_id);
        } else {
            $ticket->touchUser();
            $ticket->hasCustomer($user_id) || $ticket->linkCustomer($user_id);
        }

        foreach (getMentionedIds($message) as $mentioned_id) {
            try {
                $ticket->linkCustomer($mentioned_id);
            } catch (\Exception $ex) {
                // Bot::logException($ex); -- не надо логировать, это нормально упоминать пользователей
            }
        }
        
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        switch ($this->reason) {
            case self::BLOCK_REGULAR_GROUP:
                Bot::logComment('Сообщение в обычной группе. Выводим визиря.');
                // Визиря выводить не надо, ведь мы его уже вывели, когда бот был добавлен в группу
                // $wizard = new GroupWizard($message->getChat()->getId());
                // $wizard->show();
                // поэтому молча игнорим обычные группы
                return true; // Как будто уже обработано;
            case self::BLOCK_NO_TICKET:
                $topic_id = $message->getMessageThreadId();
                $group_id = str_replace("-100", "", $message->getChat()->getId());
                Bot::logComment("Не найден тикет для топика https://t.me/c/$group_id/$topic_id");
                return true;
            case self::BLOCK_GENERAL_TOPIC:
                //Bot::logComment('Сообщение в общем чате. Игнорим молча.');
                return true;
                
        }
        return false;
    }
}
