<?php

namespace losthost\Oberbot\controller\pre;

use losthost\telle\abst\AbstractHandlerMyChatMember;
use losthost\Oberbot\service\GroupWizard;

class WizardStartWhenChatMember extends AbstractHandlerMyChatMember {
    
    protected function check(\TelegramBot\Api\Types\ChatMemberUpdated &$chat_member): bool {
        if ($chat_member->getNewChatMember()->getStatus() == 'kicked'
         || $chat_member->getNewChatMember()->getStatus() == 'left' ) {
            return false;
        }
        return true;
    }

    protected function handle(\TelegramBot\Api\Types\ChatMemberUpdated &$chat_member): bool {
        $wizard = new GroupWizard($chat_member->getChat()->getId());
        $wizard->show();
        return true;
    }
}
