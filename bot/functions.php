<?php

namespace losthost\Oberbot;

use TelegramBot\Api\Types\Message;
use losthost\DB\DBValue;
use losthost\DB\DBView;
use losthost\telle\Bot;
use losthost\templateHelper\Template;
use losthost\BotView\BotView;
use losthost\Oberbot\data\user_chat_role;
use losthost\Oberbot\data\topic;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ArrayOfBotCommand;
use TelegramBot\Api\Types\BotCommand;
use losthost\DB\DB;

function barIndicator($value, $max_value=100, $max_bars=30) {
    $bars_symbols = ['█', '▌', '▏'];
    
    $one_bar_value = $max_value / $max_bars;
    $bars = round($value / $one_bar_value);

    if ($bars == 0) {
        return $bars_symbols[2];
    } elseif ($bars % 2) {
        return str_repeat($bars_symbols[0], ($bars-1)/2). $bars_symbols[1];
    } else {
        return str_repeat($bars_symbols[0], $bars/2);
    }
}

function __(string $text, array $vars=[]) {
    $template = new Template('translations.php', Bot::$language_code);
    $template->assign('text', $text);
    $translated = $template->process();
    
    foreach ($vars as $key => $value) {
        $translated = str_replace("%$key%", $value, $translated);
        $translated = str_replace("{{{$key}}}", $value, $translated);
    }
    
    return $translated;
}

function ifnull(mixed $value, mixed $default) {
    if (is_null($value)) {
        return $default;
    }
    return $value;
}

function sendMessage(string $text, null|array|InlineKeyboardMarkup $keyboard=null, ?int $chat_id=null, ?int $thread_id=null, $language_code=null) {
    
    if (!is_a($keyboard, InlineKeyboardMarkup::class)) {
        $keyboard = new InlineKeyboardMarkup(ifnull($keyboard, []));
    }
    
    $message = Bot::$api->sendMessage(
            ifnull($chat_id, Bot::$chat->id),
            $text, 'html',
            false, null,
            $keyboard,
            false,
            $thread_id == 1 ? null : $thread_id
    );
            
    return $message->getMessageId();
}


function message(string $type, string $text, ?string $header=null, ?int $message_tread_id=null) {
    $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
    $view->show('tpl_message', null, ['type' => $type, 'header' => __(ifnull($header, '')), 'text' => __($text)], null, $message_tread_id);
}

function getMentionedIds(Message &$message) : array {

    $result = [];
    
    if (!$message->getEntities()) {
        return $result;
    }
    
    foreach ($message->getEntities() as $entity) {
        switch ($entity->getType()) {
            case 'mention':
                $username = mb_substr($message->getText(), $entity->getOffset()+1, $entity->getLength()-1);
                $user = new DBView('SELECT id FROM [telle_users] WHERE username = ?', [$username]);
                if ($user->next()) {
                    $user_id = $user->id;
                } else {
                    $user_id = false;
                }
                break;
            case 'text_mention':
                $user_id = $entity->getUser()->getId();
                break;
            default:
                continue 2;
        }

        if ($user_id) {
            $result[] = $user_id;
        }
    }
    return $result;
}

function isChatAdministrator($user_id, $chat_id) : bool {
    
    try {
        $chat_member = Bot::$api->getChatMember($chat_id, $user_id);
    } catch (\Exception $ex) {
        return false;
    }
    switch ($chat_member->getStatus()) {
        case 'creator': 
        case 'administrator':
            return true;
        default:
            return false;
    }
}

function getChatOwner(int $chat_id) : ?\TelegramBot\Api\Types\User {
    
    try {
        $members = Bot::$api->getChatAdministrators($chat_id);
        foreach ($members as $member) {
            if ($member->getStatus() == 'creator') {
                return $member->getUser();
            }
        }
    } catch (\Exception $exc) {
        Bot::logException($exc);
    }

    return null;
}

function isAgent($user_id, $chat_id) : bool {
    return getUserChatRole($user_id, $chat_id) === user_chat_role::ROLE_AGENT;
}

function isManager($user_id, $chat_id) : bool {
    return getUserChatRole($user_id, $chat_id) === user_chat_role::ROLE_MANAGER;
}

function getUserChatRole($user_id, $chat_id) : string {
    $role = new user_chat_role(['user_id' => $user_id, 'chat_id' => $chat_id], true);
    if ($role->isNew()) {
        return user_chat_role::ROLE_CUSTOMER;
    }
    return $role->role;
}

function getQueueLen(int $ticket_id) : int {
    error_log(__FUNCTION__. " is not yet implemented.");
    return 0;
}

function getTimeEst(int $ticket_id) : ?\DateInterval {
    error_log(__FUNCTION__. " is not yet implemented.");
    return date_interval_create_from_date_string('0 sec');
}

/**
 * Очищает текст от приветствий и прочей ереси в начале для формирования
 * осмысленного названия тикета
 * 
 * @param string $text
 * @return srting
 */
function cleanup_message_for_ticket_title(string $text) : string {
   // TODO - сделать чтобы работало
    
   return $text;
}

function seconds2dateinterval(?int $seconds) : \DateInterval {
    if (is_null($seconds)) {
        $seconds = 0;
    }
    $zero = new \DateTime('@0');
    $offset = new \DateTime("@$seconds");
    return $zero->diff($offset);
}

function makePrivateCommands() {
    
    Bot::$api->setMyCommands([
            controller\command\CommandNext::getBotCommand(),
            controller\command\CommandQueue::getBotCommand(),
            controller\command\CommandAgent::getBotCommand(),
            controller\command\CommandList::getBotCommand(),
            controller\command\CommandReport::getBotCommand(),
            controller\command\CommandMyGroups::getBotCommand(),
            controller\command\CommandHelp::getBotCommand(),
            controller\command\CommandStart::getBotCommand()
    ]);
}

function makeGroupCommands() {
    Bot::$api->setMyCommands([
        controller\command\CommandHid::getBotCommand(),
        controller\command\CommandSub::getBotCommand(),
        controller\command\CommandUp::getBotCommand(),
        controller\command\CommandUnlink::getBotCommand(),
        controller\command\CommandNote::getBotCommand(),
        controller\command\CommandReport::getBotCommand(),
        controller\command\CommandDone::getBotCommand(),
        controller\command\CommandReopen::getBotCommand(),
        controller\command\CommandArchive::getBotCommand(),
        controller\command\CommandTask::getBotCommand(),
        controller\command\CommandTask::getBotCommand(),
        controller\command\CommandUrgent::getBotCommand(),
        controller\command\CommandHelp::getBotCommand()
    ], json_encode(['type' => 'all_group_chats']));
}

function makeGroupAdminCommands() {
    Bot::$api->setMyCommands([
            controller\command\CommandAgent::getBotCommand(),
            controller\command\CommandFunnel::getBotCommand(),
    ], json_encode(['type' => 'all_chat_administrators']));
}

function makeAllAgentsCommands() {

    $chat_agent = new DBView('SELECT chat_id, user_id FROM [user_chat_role] WHERE role = "agent"');
    
    while ($chat_agent->next()) {
        if (isChatAdministrator($chat_agent->user_id, $chat_agent->chat_id)) {
            makeAdminAgentCommands($chat_agent->chat_id, $chat_agent->user_id);
        } else {
            makeAgentCommands($chat_agent->chat_id, $chat_agent->user_id);
        }
    }
}

function makeAgentCommands(int $chat_id, int $user_id) {
    try {
        Bot::$api->setMyCommands([
                controller\command\CommandWait::getBotCommand(),
                controller\command\CommandHid::getBotCommand(),
                controller\command\CommandSub::getBotCommand(),
                controller\command\CommandTake::getBotCommand(),
                controller\command\CommandUnlink::getBotCommand(),
                controller\command\CommandNotify::getBotCommand(),
                controller\command\CommandNote::getBotCommand(),
                controller\command\CommandReport::getBotCommand(),
                controller\command\CommandDone::getBotCommand(),
                controller\command\CommandReopen::getBotCommand(),
                controller\command\CommandArchive::getBotCommand(),
                controller\command\CommandUp::getBotCommand(),
                controller\command\CommandDel::getBotCommand(),
                controller\command\CommandList::getBotCommand(),
                controller\command\CommandDelist::getBotCommand(),
                controller\command\CommandPause::getBotCommand(),
                controller\command\CommandContinue::getBotCommand(),
                controller\command\CommandCreate::getBotCommand(),
                controller\command\CommandOff::getBotCommand(),
                controller\command\CommandTask::getBotCommand(),
                controller\command\CommandUrgent::getBotCommand(),
                controller\command\CommandHelp::getBotCommand(),
        ], json_encode([
            'type' => 'chat_member',
            'chat_id' => $chat_id,
            'user_id' => $user_id
        ]));
    } catch (\Exception $e) {
        if ($e->getMessage() == 'Forbidden: bot was kicked from the supergroup chat') {
            cleanupChat($chat_id);
        } else {
            Bot::logException($e);
        }
    }
}

function makeAdminAgentCommands(int $chat_id, int $user_id) {
    try {
        Bot::$api->setMyCommands([
                controller\command\CommandAgent::getBotCommand(),
                controller\command\CommandCustomer::getBotCommand(),
                controller\command\CommandWait::getBotCommand(),
                controller\command\CommandHid::getBotCommand(),
                controller\command\CommandSub::getBotCommand(),
                controller\command\CommandTake::getBotCommand(),
                controller\command\CommandUnlink::getBotCommand(),
                controller\command\CommandNotify::getBotCommand(),
                controller\command\CommandNote::getBotCommand(),
                controller\command\CommandReport::getBotCommand(),
                controller\command\CommandDone::getBotCommand(),
                controller\command\CommandReopen::getBotCommand(),
                controller\command\CommandArchive::getBotCommand(),
                controller\command\CommandUp::getBotCommand(),
                controller\command\CommandDel::getBotCommand(),
                controller\command\CommandList::getBotCommand(),
                controller\command\CommandDelist::getBotCommand(),
                controller\command\CommandPause::getBotCommand(),
                controller\command\CommandContinue::getBotCommand(),
                controller\command\CommandCreate::getBotCommand(),
                controller\command\CommandOff::getBotCommand(),
                controller\command\CommandTask::getBotCommand(),
                controller\command\CommandUrgent::getBotCommand(),
                controller\command\CommandHelp::getBotCommand(),
        ], json_encode([
            'type' => 'chat_member',
            'chat_id' => $chat_id,
            'user_id' => $user_id
        ]));
    } catch (\Exception $e) {
        if ($e->getMessage() == 'Forbidden: bot was kicked from the supergroup chat') {
            cleanupChat($chat_id);
        } else {
            Bot::logException($e);
        }
    }
}

function deleteAgentCommands(int $chat_id, int $user_id) {
    try {
        Bot::$api->call('deleteMyCommands', [
            'type' => 'chat_memeber',
            'chat_id' => $chat_id, 
            'user_id' => $user_id
        ]);
    } catch (\Exception $e) {
        Bot::logException($e);
    }
}

function cleanupChat(int $chat_id) {
    $sth = DB::prepare('DELETE FROM [user_chat_role] WHERE chat_id=?');
    $sth->execute([$chat_id]);
    
    /// TODO - возможно надо чистить и другие, связанные с четом таблицы, но я опасаюсь,
    //  что это может навредить при случайном удалении бота из чата
}
