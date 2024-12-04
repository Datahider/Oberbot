<?php

namespace losthost\Oberbot\controller\action;

use losthost\Oberbot\data\chat_group;
use losthost\Oberbot\data\session;
use losthost\telle\Bot;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

use function \losthost\Oberbot\__;

class ActionActiveListDisplay {
    
    static public function do(int $user_id, int $chat_id, int $message_to_edit=null) {

        $agent_lists = chat_group::getUserLists($user_id);
        $active = static::getActiveList($user_id);
        $active = $active == 'all' ? __('all') : $active;
        
        $keyboard = [[['text' => __('all'), 'callback_data' => 'list_all']]];
        
        if (!empty($agent_lists)) {
            foreach ($agent_lists as $list) {
                $keyboard[] = [['text' => $list, 'callback_data' => "list_$list"]];
            }
        }
        
        $text = __("Текущий активный список групп: <b>%active%</b>\n\nДля получения следующей задачи нажмите /next\n\nДля изменения активного списка нажмите кнопку:", ['active' => $active]);
        $reply_markup = new InlineKeyboardMarkup($keyboard);
        
        if ($message_to_edit) {
            try {
                Bot::$api->editMessageText($chat_id, $message_to_edit, $text, 'HTML', false, $reply_markup);
                return;
            } catch (Exception $ex) {
                Bot::logException($ex);
            }
        }
        Bot::$api->sendMessage($chat_id, $text, 'HTML', false, null, $reply_markup);
    }
    
    static public function getActiveList(int $user_id) {
        $session = new session(['user_id' => $user_id, 'chat_id' => $user_id], true);
        $active = !$session->working_group ? 'all' : $session->working_group;
        return $active;
    }
}
