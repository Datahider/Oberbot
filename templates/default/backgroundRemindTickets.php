<b>Пожалуйста обратите внимание:</b>
<?php

use function losthost\Oberbot\ticketMentionNoId;
use losthost\Oberbot\view\Emoji;

use function \losthost\Oberbot\__;

$index = 0;
foreach ($tickets as $ticket) {
    $index++;
    
    $mention = ticketMentionNoId($ticket);
    $icon = Emoji::TEXT_EMOJI_BY_TYPE[$ticket->type];
    $status = __('status_'. $ticket->status);
    echo "<b>$index.</b> $icon $mention ($status)\n";
}
