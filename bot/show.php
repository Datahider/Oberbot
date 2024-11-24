<?php

namespace losthost\Oberbot;

use losthost\Oberbot\data\user;
use losthost\Oberbot\data\topic;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\OberbotModel\Model;
use losthost\DB\DBView;

function mention(user $user) : string {
    return "<a href=tg://user?id=$user->tg_id>$user->name</a>";
}

function mentionById(int $tg_id, bool $use_usernames = false) {
    
    $user = new DBView("SELECT first_name, last_name, username FROM [telle_users] WHERE id = ?", [$tg_id]);
    if ($user->next()) {
        if ($use_usernames && $user->username) {
            return "@$user->username";
        } else {
            return "<a href=\"tg://user?id=$tg_id\">$user->first_name</a>";
        }
    } else {
        return "Уважаемый пользователь";
    }
    
}

function mentionByIdArray(array $tg_ids, mixed $show_none='', bool $use_usernames = false) {
    
    if (!empty($tg_ids)) {
        $text = '';
        foreach ($tg_ids as $tg_id) {
            $text .= ', '. mentionById($tg_id, $use_usernames);
        }
        return substr($text, 2);
    } 
    
    return $show_none;
}

function mentionByView(DBView $view, mixed $show_none='', bool $use_usernames = false, string $id_field_name='id') {
    
    $mentions = [];
    
    while ($view->next()) {
        $mentions[] = mentionById($view->$id_field_name, $use_usernames);
    }
    return empty($mentions) ? $show_none : implode(', ', $mentions);
}

function showNewTopicGreating(topic $ticket) {
    $view = new BotView(Bot::$api, $ticket->chat_id, Bot::$language_code);
    $view->show('tpl_new_topic_greating', 'kbd_new_topic_greating_full', ['topic' => $ticket, 'queue_len' => getQueueLen()], null, $ticket->topic_id);
}

function showTimerStarted(topic $topic, user $user) {
    
    $model = new Model();
    $customers = $topic->getCustomers($topic);
    
    $view = new BotView(Bot::$api, $topic->chat_id, Bot::$language_code);
    $view->show();
    
    
}

