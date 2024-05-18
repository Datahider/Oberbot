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
        $correct_file = preg_replace("/([^\\\\\/]+?)$/", 'Текущие заявки и задачи.md', $tmp_file);
        rename($tmp_file, $correct_file);
        file_put_contents($correct_file, $text);

        Bot::$api->sendDocument(Bot::$chat->id, new \CURLFile($correct_file));
        return true;
    }

}
