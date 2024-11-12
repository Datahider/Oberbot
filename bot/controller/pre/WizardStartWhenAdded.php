<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMessage;
use losthost\Oberbot\service\GroupWizard;
use losthost\telle\Bot;

class WizardStartWhenAdded extends AbstractHandlerMessage {
    
    protected function check(\TelegramBot\Api\Types\Message &$message): bool {
        $new_members = $message->getNewChatMembers();
        $my_id = Bot::param('bot_userid', null);
        
        if ($new_members) {
            foreach ($new_members as $member) {
                if ($member->getId() == $my_id) {
                    return true;
                }
            }
        } 
        return false;
    }

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $wizard = new GroupWizard($message->getChat()->getId());
        $wizard->show();
        return true;
        
    }
}
