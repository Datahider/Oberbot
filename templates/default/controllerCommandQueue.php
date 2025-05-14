Очередь заявок для списка <?=$list;?>

<?php

use losthost\Oberbot\data\ticket;

use function losthost\Oberbot\ticketMention;
use function losthost\Oberbot\ticketMentionNoId;
use losthost\Oberbot\view\Emoji;

use function \losthost\Oberbot\__;

$index = 0;
foreach ($queue as $ticket) {
    $index++;
    
    $mention = ticketMentionNoId($ticket);
    $icon = Emoji::TEXT_EMOJI_BY_TYPE[$ticket->type];
    $status = __('status_'. $ticket->status);
    echo "<b>$index.</b> $icon $mention ($status)\n";
}
