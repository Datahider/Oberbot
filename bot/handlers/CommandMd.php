<?php

namespace losthost\Oberbot\handlers;

use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\telle\abst\AbstractHandlerCommand;
use losthost\OberbotModel\builders\MdReport;
use losthost\Oberbot\data\session;
use losthost\templateHelper\Template;

class CommandMd extends AbstractHandlerCommand {
    
    const COMMAND = 'md';

    protected function handle(\TelegramBot\Api\Types\Message &$message): bool {
        
        $cmd = $message->getText();
        $m = [];
        
        $session = new session(['user_id' => Bot::$user->id, 'chat_id' => Bot::$chat->id], true);
        if (preg_match("/\/md\s(.*)$/", $cmd, $m)) {
            $session->working_group = $m[1];
        } elseif (!$session->working_group) {
            $session->working_group = 'all';
        }
        $session->isModified() && $session->write();
        
        $report = new MdReport();
        $result = $report->build(['group' => $session->working_group]);
        
        $template = new Template('cmd_md.php', Bot::$language_code);
        $template->assign('result', $result);
        $template->assign('working_group', $session->working_group);
        $text = $template->process();
        
        $tmp_file = tempnam('/tmp', 'md_');
        $hr_name = 'Текущие заявки и задачи.md';
        file_put_contents($tmp_file, $text);

        Bot::$api->sendDocument(Bot::$chat->id, new \CURLFile($tmp_file, 'text/markdown', $hr_name));
        return true;
    }

}
