<?php

namespace losthost\Oberbot\controller\action;

use losthost\telle\Bot;

class ActionUpdateCommandMenu {
    
    static public function do() {
        
        static::updatePrivateMenu();
        static::updateGroupMenu();
        static::updateGroupAdminMenu();
    }
}
