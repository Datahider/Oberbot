<?php

use losthost\Oberbot\service\Service;

$mention = Service::mentionById($user_id);

echo "$mention начал работу.";
