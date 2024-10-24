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

function mentionById(int $tg_id) {
    
    $user = new DBView("SELECT first_name, last_name, username FROM [telle_users] WHERE id = ?", [$tg_id]);
    if ($user->next()) {
        if ($user->username) {
            return "@$user->username";
        } else {
            return "<a href=tg://user?id=$tg_id>$user->first_name</a>";
        }
    } else {
        return "<a href=tg://user?id=$tg_id>&lt;Неизвестный&gt;</a>";
    }
    
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