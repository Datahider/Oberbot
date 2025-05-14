<?php

namespace losthost\Oberbot\view;

use losthost\Oberbot\data\ticket;

class Emoji {

//    const TICKET_REGULAR = '🛟';
    const TICKET_REGULAR = '❗';
    
    const TICKET_URGENT = '🆘';
    const TASK_REGULAR = '🎓️';
    const TASK_PRIORITY = '⭐️';
    
    const ACTION_PRIORITY_UP = '🔺';
    const ACTION_PRIORITY_DOWN = '🔻';
    const ACTION_PLAY = '▶️';
    const ACTION_PAUSE = '⏸';
    const ACTION_NOTIFY = '🛎';
    const ACTION_DONE = '✅';


    const ICON_SOS = '🆘';
//    const ICON_LIFEBUOY = '🛟';
    const ICON_LIFEBUOY = '❗';
    const ICON_EXCLAMATION = '❗️';
    const ICON_EXCLAMATION_2 = '‼️';
    const ICON_CONSULT = '🗣';
    const ICON_URGENT_CONSULT = '👑';
    const ICON_DONE = '✅';
    const ICON_TODO = '🔲';
    const ICON_FUNNEL = '💎';
    const ICON_FIRE = '🔥';
    const ICON_BOT = '🤖';
    const ICON_PRIVATE = '🔞';

    const ICON_1 = '1️⃣';
    const ICON_2 = '2️⃣';
    const ICON_3 = '3️⃣';
    const ICON_4 = '4️⃣';
    const ICON_5 = '5️⃣';
    
    const RATING_GOOD = '😊';
    const RATING_ACCEPTABLE = '😐';
    const RATING_BAD = '🙁';
    
    const ID_FINISH = 5408906741125490282;
    const ID_URGENT = 5312241539987020022;
    const ID_TOP = 5418085807791545980;
    const ID_STAR = 5235579393115438657;
    const ID_TASK = 5357419403325481346;
    const ID_DOUBLE_EXCLAMATION = 5377498341074542641;
    const ID_EXCLAMATION = 5379748062124056162;
    const ID_ARCHIVE = 5348227245599105972;
    const ID_QUESTION = 5377316857231450742;
    const ID_SCHEDULED_CONSULT = 5370870893004203704;
    const ID_URGENT_CONSULT = 5357107601584693888;
    const ID_VOICE = 5370870893004203704;
    const ID_CROWN = 5357107601584693888;
    const ID_FIRE = 5312241539987020022;
    const ID_BOT = 5309832892262654231;
    const ID_PRIVATE = 5420331611830886484;
    
    const ID_NONE = null;

    const TOPIC_ICONS_BY_TYPE = [
        ticket::TYPE_REGULAR_TASK           => self::ID_NONE,
        ticket::TYPE_PRIORITY_TASK          => self::ID_STAR,
        ticket::TYPE_MALFUNCTION            => self::ID_EXCLAMATION,
        ticket::TYPE_SCHEDULED_CONSULT      => self::ID_VOICE,
        ticket::TYPE_URGENT_CONSULT         => self::ID_CROWN,
        ticket::TYPE_MALFUNCTION_MULTIUSER  => self::ID_DOUBLE_EXCLAMATION,
        ticket::TYPE_MALFUNCTION_FREE       => self::ID_FIRE,
        ticket::TYPE_BOT_SUPPORT            => self::ID_BOT,
        ticket::TYPE_PRIVATE_SUPPORT        => self::ID_PRIVATE,
    ];
    
    const TEXT_EMOJI_BY_TYPE = [
        ticket::TYPE_REGULAR_TASK           => self::TASK_REGULAR,
        ticket::TYPE_PRIORITY_TASK          => self::TASK_PRIORITY,
        ticket::TYPE_MALFUNCTION            => self::ICON_EXCLAMATION,
        ticket::TYPE_SCHEDULED_CONSULT      => self::ICON_CONSULT,
        ticket::TYPE_URGENT_CONSULT         => self::ICON_URGENT_CONSULT,
        ticket::TYPE_MALFUNCTION_MULTIUSER  => self::ICON_EXCLAMATION_2,
        ticket::TYPE_MALFUNCTION_FREE       => self::ICON_FIRE,
        ticket::TYPE_BOT_SUPPORT            => self::ICON_BOT,
        ticket::TYPE_PRIVATE_SUPPORT        => self::ICON_PRIVATE,
    ];
}
