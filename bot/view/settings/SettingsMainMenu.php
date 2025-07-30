<?php

namespace losthost\Oberbot\view\settings;

use function \losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;
use losthost\Oberbot\data\chat_settings;
use losthost\DB\DBList;
use losthost\telle\Bot;

class SettingsMainMenu {
    
    public function show() {
        
        $button_create = ['text' => __('Создать новый'), 'callback_data' => 'settings_create'];
        $settings = (new DBList(chat_settings::class, ['owner_id' => Bot::$user->id]))->asArray();
        
        if (count($settings) === 0) {
            sendMessage(__('У вас нет настроек'), [[$button_create]]);
        } else {
            $keyboard = [];

            foreach ($settings as $i => $set) {
                $keyboard[intdiv($i, 2)][] = ['text' => $set->name, 'callback_data' => 'settings_'. $set->id];
            }        
            $keyboard[] = [$button_create];
            sendMessage(__('Выберите настройку'), $keyboard);
        }
        
    }
}
