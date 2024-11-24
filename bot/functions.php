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
    }
    
    return $translated;
}

function ifnull(mixed $value, mixed $default) {
    if (is_null($value)) {
        return $default;
    }
    return $value;
}

function sendMessage(string $text, ?array $keyboard=null, ?int $chat_id=null, ?int $thread_id=null, $language_code=null) {
    
    $message = Bot::$api->sendMessage(
            ifnull($chat_id, Bot::$chat->id),
            $text, 'html',
            false, null,
            new InlineKeyboardMarkup(ifnull($keyboard, [])),
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
    
    $chat_member = Bot::$api->getChatMember($chat_id, $user_id);
    switch ($chat_member->getStatus()) {
        case 'creator': 
        case 'administrator':
            return true;
        default:
            return false;
    }
}

function isAgent($user_id, $chat_id) : bool {
    return getUserChatRole($user_id, $chat_id) === user_chat_role::ROLE_AGENT;
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

