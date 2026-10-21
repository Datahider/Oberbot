<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

use RuntimeException;

final class NativeAiProcessRunner implements AiProcessRunnerInterface
{
    public function run(array $command): array
    {
        $pipes = [];
        $process = proc_open($command, [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);
        if (!is_resource($process)) throw new RuntimeException('Cannot start AI transport submit command');
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit_code = proc_close($process);
        return ['exit_code' => $exit_code, 'stdout' => (string) $stdout, 'stderr' => (string) $stderr];
    }
}
