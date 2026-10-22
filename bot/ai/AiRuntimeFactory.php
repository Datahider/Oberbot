<?php

declare(strict_types=1);

namespace losthost\Oberbot\ai;

use losthost\telle\Bot;
use RuntimeException;

final class AiRuntimeFactory
{
    public static function createLogStore(): DbAiTicketLogStore
    {
        return new DbAiTicketLogStore();
    }

    public static function createHandoffService(): AiHandoffService
    {
        $temp_dir = trim((string) Bot::param('ai_temp_dir', '/home/web/tmp'));
        return new AiHandoffService(
            self::createLogStore(),
            new TelegramAiAttachmentPublisher(
                Bot::$api,
                self::requiredParam('ai_file_exchange_base_url'),
                self::requiredParam('ai_file_exchange_token'),
                $temp_dir
            ),
            new LocalAiTransportSubmitter(
                self::requiredParam('ai_transport_submit_script'),
                self::requiredParam('ai_transport_config_path'),
                self::requiredParam('ai_transport_submit_secret'),
                $temp_dir,
                new NativeAiProcessRunner()
            ),
            $temp_dir
        );
    }

    public static function timerSubject(): string
    {
        return self::requiredParam('ai_timer_subject');
    }

    private static function requiredParam(string $name): string
    {
        $value = trim((string) Bot::param($name, ''));
        if ($value === '') throw new RuntimeException("Missing required bot parameter: {$name}");
        return $value;
    }
}
