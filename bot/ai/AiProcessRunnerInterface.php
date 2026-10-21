<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

interface AiProcessRunnerInterface
{
    /** @param list<string> $command @return array{exit_code:int,stdout:string,stderr:string} */
    public function run(array $command): array;
}
