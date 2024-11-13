<?php

namespace losthost\Oberbot\view;

use losthost\DB\DBTracker;
use losthost\Oberbot\data\note;
use losthost\telle\Bot;
use losthost\BotView\BotView;

class NoteCreation extends DBTracker {
    
    public function track(\losthost\DB\DBEvent $event) {
        
        $note = $event->object;
        
        $view = new BotView(Bot::$api, $note->chat_id, Bot::$language_code);
        $view->show('viewNoteCreation', 'kbdNoteCreation', ['note' => $note], null, $note->topic_id);
        
    }
}
