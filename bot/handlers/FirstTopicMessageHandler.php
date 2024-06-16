<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\data\topic;
use losthost\Oberbot\data\topic_user;
use losthost\Oberbot\data\topic_admin;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\DB\DB;

use function \losthost\Oberbot\getMentionedIds;
use function \losthost\Oberbot\isAgent;

class FirstTopicMessageHandler extends AbstractHandlerMessage {
    
    protected topic $topic;
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $topic_id = $message->getMessageThreadId();
        if ($topic_id && $topic_id > 1) {
            $topic = new topic(['topic_id' => $topic_id, 'status' => topic::STATUS_PENDING], true);
            if ($topic->isNew()) {
                return false;
            }
            return true;
        }
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        DB::beginTransaction();
        try {
            $this->topic = new topic(['topic_id' => $message->getMessageThreadId()]);
            $this->process();
            $this->processMentions($message);
            DB::commit();
        } catch (\Exception $e) {
            Bot::logException($e);
            DB::rollBack();
        }    
        return true;
    }
    
    protected function newTopicTitle(topic &$topic) {
        $text_id = " #$topic->id";
        $max_len = 128 - strlen($text_id);
        return substr($topic->topic_title, 0, $max_len). $text_id;
    }
    
    public function process() {

        $this->topic->topic_title = $this->newTopicTitle($this->topic);
        $this->topic->status = topic::STATUS_NEW;

        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        
        $this->topic->write();

        // TODO - добавить настоящее положение в очереди
        $view->show('tpl_new_topic_greating', 'kbd_new_topic_greating_full', ['topic' => $this->topic, 'queue_len' => 1], null, $this->topic->topic_id);
        Bot::$api->editForumTopic(Bot::$chat->id, $this->topic->topic_id, $this->topic->topic_title);
    }
    
    protected function processMentions(\TelegramBot\Api\Types\Message &$message) {
        
        $ids = getMentionedIds($message);
        
        if (count($ids) === 0) {
            // Добавим отправителя как кастомера
            $ticket_user = new topic_user(['topic_number' => $this->topic->id, 'user_id' => $message->getFrom()->getId()], true);
            $ticket_user->isNew() && $ticket_user->write();
        } else {
            $chat_id = $message->getChat()->getId();
            $from_id = $message->getFrom()->getId();
            
            if (!isAgent($from_id, $chat_id)) {
                // Добавим отправителя как кастомера если он не агент
                $ticket_user = new topic_user(['topic_number' => $this->topic->id, 'user_id' => $from_id], true);
                $ticket_user->isNew() && $ticket_user->write();
            } else {
                $ticket_admin = new topic_admin(['topic_number' => $this->topic->id, 'user_id' => $from_id], true);
                $ticket_admin->isNew() && $ticket_admin->write();
            }
            
            // Добавим так же всех упомянутых
            foreach (getMentionedIds($message) as $id) {
                if (!isAgent($id, $chat_id) || isAgent($from_id, $chat_id)) {
                    $ticket_user = new topic_user(['topic_number' => $this->topic->id, 'user_id' => $id], true);
                    $ticket_user->isNew() && $ticket_user->write();
                }
            }
        }
        
    }
}
