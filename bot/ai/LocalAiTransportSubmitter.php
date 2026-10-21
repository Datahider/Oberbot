<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

use RuntimeException;

final class LocalAiTransportSubmitter implements AiTransportSubmitterInterface
{
    public function __construct(
        private string $submit_script,
        private string $transport_config,
        private string $secret,
        private string $temp_dir,
        private AiProcessRunnerInterface $runner
    ) {}

    public function submit(array $job): int
    {
        foreach ([$this->submit_script, $this->transport_config, $this->secret, $this->temp_dir] as $value) {
            if (trim($value) === '') throw new RuntimeException('AI transport submit configuration is incomplete');
        }
        if (!is_file($this->submit_script)) throw new RuntimeException('AI transport submit script does not exist');
        if (!is_file($this->transport_config)) throw new RuntimeException('AI transport config does not exist');
        if (!is_dir($this->temp_dir) && !mkdir($this->temp_dir, 0770, true) && !is_dir($this->temp_dir)) {
            throw new RuntimeException('Cannot create AI temporary directory');
        }
        $job_path = tempnam($this->temp_dir, 'oberbot-ai-job-');
        if ($job_path === false) throw new RuntimeException('Cannot create AI transport job file');
        $job['secret'] = $this->secret;
        try {
            $json = json_encode($job, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            if (file_put_contents($job_path, $json, LOCK_EX) === false) throw new RuntimeException('Cannot write AI transport job file');
            $result = $this->runner->run(['php', $this->submit_script, $this->transport_config, $job_path]);
            if ($result['exit_code'] !== 0) throw new RuntimeException('AI transport submit command failed: ' . trim($result['stderr']));
            $response = json_decode($result['stdout'], true, 512, JSON_THROW_ON_ERROR);
            $thread_id = (int) ($response['hidden_thread_id'] ?? 0);
            if (($response['accepted'] ?? false) !== true || $thread_id < 1) throw new RuntimeException('AI transport returned invalid response');
            return $thread_id;
        } finally {
            @unlink($job_path);
        }
    }
}
