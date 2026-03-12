<?php

namespace losthost\Oberbot\controller\reaction;

use losthost\telle\abst\AbstractHandlerMessageReaction;
use losthost\Oberbot\data\chat;
use losthost\Oberbot\data\reaction_settings;
use losthost\telle\Bot;
use losthost\Oberbot\data\message_info;

use function losthost\Oberbot\sendMessage;
use function \losthost\Oberbot\__;
use function \losthost\Oberbot\isAgent;

class Reaction extends AbstractHandlerMessageReaction {
    
    protected string $reaction_emoji;
    protected ?int $settings_id;
    protected ?int $message_thread_id;
    protected int  $message_user_id;
    protected int  $chat_id;
    protected int  $message_id;

    #[\Override]
    protected function check(\TelegramBot\Api\Types\MessageReactionUpdated &$message_reaction): bool {
        $new_reactions = $message_reaction->getNewReaction();
        $this->chat_id = $message_reaction->getChat()->getId();
        $this->message_id = $message_reaction->getMessageId();
              
        
        foreach ($new_reactions ?? [] as $new_reaction) {
            if (isAgent($message_reaction->getUser()->getId(), $this->chat_id) && $new_reaction->getType() == 'emoji') {
                $this->reaction_emoji = $new_reaction->getEmoji();
                $chat = new chat(['id' => $this->chat_id], true);
                $this->settings_id = $chat->chat_settings_id; 
                $message_info = new message_info(['chat_id' => $this->chat_id, 'message_id' => $this->message_id], true);
                $this->message_thread_id = $message_info->thread_id;
                $this->message_user_id = $message_info->user_id;
                return true && $this->settings_id;
            }
        }
        return false;
    }

    #[\Override]
    protected function handle(\TelegramBot\Api\Types\MessageReactionUpdated &$message_reaction): bool {
    
        $reaction = new reaction_settings(['chat_settings_id' => $this->settings_id, 'reaction' => $this->reaction_emoji], true);

        if ($reaction->message) {
            Bot::$api->sendMessage($message_reaction->getChat()->getId(), $reaction->message, 'HTML', false, $message_reaction->getMessageId(), null, false, $this->message_thread_id);
        }

        switch ($reaction->action) {
            case 'ban':
                $this->banUser($reaction->action_param);
                break;
            case 'kick':
                $this->kickUser($reaction->action_param);
                break;
            case 'custom':
                $handler = new $reaction->custom_action_class;
                if (is_a($handler, \losthost\Oberbot\service\AbstractCustomAction::class)) {
                    $handler->do($this->message_id, $this->chat_id, $this->message_thread_id, $this->message_user_id, $reaction->action_param);
                } else {
                    throw new \RuntimeException("$reaction->custom_action_class is not a descendant of AbstractCustomAction.");
                }
                break;
            default:
                // Do nothing
        }
        return true;
    }
    
    protected function banUser(?int $minutes) {
        
        $until = $minutes ? time() + 60 * $minutes : null;
        Bot::$api->restrictChatMember($this->chat_id, $this->message_user_id, $until);
        
    }
    
    protected function kickUser(?int $minutes) {

        $until = $minutes ? time() + 60 * $minutes : null;
        Bot::$api->kickChatMember($this->chat_id, $this->message_user_id, $until);
        
    }
    
}
