<?php

namespace App\Services\SxopeLogService;

use App\Helpers\GoogleCloudErrorReportingHelper;
use App\Services\OpenTelemetryService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Utils;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseSxopeLogService
{
    public const GUZZLE_TIMEOUT_SEC = 30;

    abstract public static function sendToBiqQuery(array $records): array|false;

    public static function sendToRabbitMQ(array $data, string $queueName): void
    {
        $queueLimit = config('sxope-logging.queue-limit');
        $queueSize = self::getQueueSize($queueName);

        if ($queueLimit > 0 && $queueSize < $queueLimit) {
            $jsonData = Utils::jsonEncode($data);
            App::terminating(function () use ($jsonData, $queueName) {
                traceInSpan("queue-push-raw: {$queueName}", function () use ($jsonData, $queueName) {
                    Queue::pushRaw($jsonData, $queueName);
                });
            });
        } else {
            logException(new Exception("sxope-logging queue {$queueName} is full. Size: {$queueSize}, queue limit: {$queueLimit}."));
        }
    }

    public static function getQueueSizeCacheKey(string $queueName): string
    {
        return "sxope-logging-queue-{$queueName}-size-cache";
    }

    public static function getQueueSize(string $queueName): int
    {
        $queueSizeCacheKey = self::getQueueSizeCacheKey($queueName);

        $queueSizeCache = Cache::get($queueSizeCacheKey);

        if (
            $queueSizeCache === null
            || ! isset($queueSizeCache['time'])
            || ! isset($queueSizeCache['count'])
            ||  $queueSizeCache['time'] < (time() - 120)
        ) {
            // update value from request when there no or old value
            $queueSizeCache = self::updateQueueSizeCache($queueName);
        }
        return (int) $queueSizeCache['count'];
    }

    public static function updateQueueSizeCache(string $queueName): array
    {
        $queueSizeCacheKey = self::getQueueSizeCacheKey($queueName);
        $queueSize = traceInSpan("queue-get-size: {$queueName}", fn() => Queue::size($queueName));
        $queueSizeCache = [
            'count' => $queueSize,
            'time' => time(),
        ];
        Cache::forever($queueSizeCacheKey, $queueSizeCache);
        return $queueSizeCache;
    }

    private static function getClient(): Client
    {
        $guzzleConfig = [
            'connect_timeout' => self::GUZZLE_TIMEOUT_SEC,
            'timeout' => self::GUZZLE_TIMEOUT_SEC,
        ];

        return new Client($guzzleConfig);
    }

    protected static function makeRequest(string $method, string $url, array $data): array|false
    {
        try {
            $response = self::getClient()->{$method}(
                $url,
                [
                    'headers' => [
                        'X-API-KEY' => config('sxope-logging.api-key'),
                        'X-Requested-With' => 'XMLHttpRequest',
                        'Content-Type' => 'application/json',
                        'Connection' => 'close',
                    ],
                    'body' => Utils::jsonEncode($data),
                    'verify' => config('app.verify_service_ssl'),
                    'curl' => [
                        CURLOPT_FORBID_REUSE => true,
                        CURLOPT_FRESH_CONNECT => true,
                    ],
                ]
            );
        } catch (TransferException $e) {
            self::logGuzzleException($data, null, $e);

            return false;
        }

        if (!in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            self::logGuzzleException($data, $response);

            return false;
        }

        return json_decode($response->getBody(), true);
    }

    public static function logGuzzleException(array $records, ?ResponseInterface $response, ?TransferException $exception = null): void
    {
        $message = 'BQ Logging Failed!';
        Log::error($message);

        if ($exception) {
            Log::error('Exception:');
            Log::error($exception);
        }

        Log::error('Content:');
        Log::error($records);

        if ($response) {
            Log::error('Logging service response:');
            Log::error(json_decode($response->getBody()->getContents(), true));
        }

        if (!App::environment(['local']) && extension_loaded('newrelic')) { // Ensure PHP agent is available
            newrelic_notice_error($exception ?? $message);
        }

        // notify gcloud
        GoogleCloudErrorReportingHelper::logException($exception);

        OpenTelemetryService::logException($exception);
    }

    public static function getAppContext(): string
    {
        return config('app.name') . ' (' . config('app.env') . ')';
    }
}
