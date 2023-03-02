<?php

namespace App\Services\SxopeLogService;

use function config;

class SxopeChangeLogService extends BaseSxopeLogService
{
    public static function queueLogMessage(array $data): void
    {
        if (!config('sxope-logging.enabled_change_log')) {
            return;
        }

        self::sendToRabbitMQ($data, config('sxope-logging.queue_change_log'));
    }

    public static function sendToBiqQuery(array $records): array|false
    {
        return self::makeRequest('put', config('sxope-logging.api-url-change-log'), ['entries' => $records]);
    }

    public static function getChangeHistoryLog(array $request): array|false
    {
        $url = config('sxope-logging.api-url-change-log');
        return self::makeRequest('get', $url, $request);
    }
}
