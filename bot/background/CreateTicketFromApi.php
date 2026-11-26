<?php

namespace losthost\Oberbot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\telle\Bot;
use losthost\Oberbot\api\ApiTicketRequestProcessor;
use losthost\Oberbot\api\DbApiTicketRequestStore;
use losthost\Oberbot\api\ActionApiTicketCreator;

class CreateTicketFromApi extends AbstractBackgroundProcess {
    
    public function run() {
        Bot::$language_code = 'ru';
        $processor = new ApiTicketRequestProcessor(new DbApiTicketRequestStore(), new ActionApiTicketCreator());
        $processor->process((int) $this->param);
    }
}
