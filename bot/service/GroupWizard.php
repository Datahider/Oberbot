<?php

namespace losthost\Oberbot\service;

use losthost\Oberbot\data\chat; 
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\Oberbot\service\Service;
use losthost\Oberbot\data\user_meta;

use function losthost\Oberbot\__;
use function losthost\Oberbot\mentionById;

class GroupWizard {
    
    protected chat $chat;
    
    public function __construct(int $chat_id) {
        $this->chat = new chat(['id' => $chat_id], true);
        if ($this->chat->isNew()) {
            $this->chat->process_tickets = false;
            $this->chat->language_code = Bot::$language_code;
            $this->chat->delete_commands = false;
            $this->chat->write('', ['mute' => true]);
        } 
        
        
    }
    
    public function show() {
        
        $is_forum = $this->isForum();
        $is_admin = $this->isAdministrator();
        
        $view = new BotView(Bot::$api, $this->chat->id, $this->chat->language_code);
        $this->chat->wizard_message_id = $view->show('viewGroupWizard', null, compact('is_forum', 'is_admin'), $this->chat->wizard_message_id);
        
        if ($is_forum && $is_admin) {
            $this->chat->wizard_message_id = null;
            Service::message('info', "Проверка группы завершена.");
            
            if (user_meta::get(Bot::$user->id, 'AddAgentTip', 'on') == 'on') {
                $view = new BotView(Bot::$api, $this->chat->id, Bot::$language_code);
                $view->show('viewTip', 'kbdTip', [
                    'tip_text' => sprintf(__('AddAgentTip'), mentionById(Bot::$user->id, true)),
                    'tip_name' => 'AddAgentTip',
                ]);
            }
        }
        $this->chat->isModified() && $this->chat->write('', ['mute' => true]);
        
        
    }
    
    protected function isForum() {
        $full_chat_info = Bot::$api->getChat($this->chat->id);
        if ($full_chat_info->getIsForum()) {
            return true;
        }
        return false;
    }
    
    protected function isAdministrator() {
        $member = Bot::$api->getChatMember($this->chat->id, Bot::param('bot_userid', null));
        if ($member->getStatus() === 'administrator') {
            return true;
        }
        return false;
        
    }
}
