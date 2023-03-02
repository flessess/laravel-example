<?php

declare(strict_types=1);

namespace App\Providers;

use App\Api\DataApi\Contracts\DataApiClientInterface;
use App\Api\DataApi\Contracts\GatewayDataApiClientInterface;
use App\Api\DataApi\DataApiClient;
use App\Api\GatewayApi\GatewayApiClient;
use App\Api\Iam\Contracts\GatewayIamApiClientInterface;
use App\Api\Iam\Contracts\IamApiClientInterface;
use App\Api\Iam\IamApiClient;
use App\Api\Ocr\OcrApi;
use App\Api\Ocr\OcrApiInterface;
use App\Api\OcrElastic\OcrElasticApi;
use App\Api\OcrElastic\OcrElasticApiInterface;
use App\Api\PersonaApi\Contracts\GatewayPersonaApiClientInterface;
use App\Api\PersonaApi\Contracts\PersonaApiClientInterface;
use App\Api\PersonaApi\PersonaApiClient;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Inventcorp\SxopeApiGatewayClient\Configuration;
use Sxope\Facades\Sxope;
use Sxope\ValueObjects\Id;

class ApiClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerPersonaApi();
        $this->registerOcrApi();
        $this->registerOcrElasticApi();
        $this->registerDataApi();
        $this->registerIamApi();
        $this->registerGatewayApi();
    }

    protected function registerPersonaApi(): void
    {
        $this->app->bind( GatewayPersonaApiClientInterface::class, function () {
            $host = rtrim(config('five-star-api.host'), '/') . '/api/user-service/v1/persona/';

            $config = [
                'verify' => config('app.verify_service_ssl'),
                'base_uri' => $host,
                'headers' => [
                    'accept' => 'application/json',
                    'content-Type' => 'application/json',
                    'x-api-key' => config('five-star-api.key'),
                    'x-sso-user-id' => Sxope::getCurrentUserId()->getUuid()
                ],
            ];

            $client = new Client($config);

            return new PersonaApiClient($client);
        });

        $this->app->bind(PersonaApiClientInterface::class, function () {
            $host = rtrim(config('persona-api.host'), '/') . '/';

            $config = [
                'verify' => config('app.verify_service_ssl'),
                'base_uri' => $host,
                'headers' => [
                    'accept' => 'application/json',
                    'content-Type' => 'application/json',
                    'x-api-key' => config('persona-api.api-key'),
                ],
            ];

            $client = new Client($config);

            return new PersonaApiClient($client);
        });
    }

    protected function registerOcrApi(): void
    {
        $this->app->bind(OcrApiInterface::class, function () {
            $host = config('ocr-api.host') . '/';

            $config = [
                'verify' => config('app.verify_service_ssl'),
                'base_uri' => $host,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => config('ocr-api.api-key'),
                    'Connection' => 'close',
                ],
            ];

            $client = new Client($config);

            return new OcrApi($client);
        });
    }

    protected function registerOcrElasticApi(): void
    {
        $this->app->bind(OcrElasticApiInterface::class, function () {
            $host = config('ocr-elastic-api.host') . '/api/';

            $config = [
                'verify' => config('app.verify_service_ssl'),
                'base_uri' => $host,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Connection' => 'close',
                ],
            ];

            $client = new Client($config);

            return new OcrElasticApi($client);
        });
    }

    /**
     * Register api-data class in the application.
     */
    protected function registerDataApi(): void
    {
        $this->app->bind(GatewayDataApiClientInterface::class, function () {
            $host = rtrim(config('five-star-api.host'), '/') . '/api/user-service/v1/data/v1/';

            $config = [
                'verify' => config('app.verify_service_ssl'),
                'base_uri' => $host,
                'headers' => [
                    'accept' => 'application/json',
                    'content-Type' => 'application/json',
                    'x-api-key' => config('five-star-api.key'),
                    'x-sso-user-id' => Sxope::getCurrentUserId()->getUuid()
                ],
            ];

            $client = new Client($config);

            return new DataApiClient($client);
        });
        $this->app->bind(DataApiClientInterface::class, function () {
            $host =  rtrim(str_replace('gateway', 'api-data', config('five-star-api.host')), '/') . '/api/v1/';

            $config = [
                'verify' => config('app.verify_service_ssl'),
                'base_uri' => $host,
                'headers' => [
                    'accept' => 'application/json',
                    'content-Type' => 'application/json',
                    'x-api-key' => config('five-star-api.key')
                ],
            ];

            $client = new Client($config);

            return new DataApiClient($client);
        });
    }

    public function registerIamApi(): void
    {
        $this->app->singleton(IamApiClientInterface::class, function () {
            $host = rtrim(config('iam-api.host'), '/') . '/api/';

            $config = [
                'verify' => config('app.verify_service_ssl'),
                'base_uri' => $host,
                'headers' => [
                    'accept' => 'application/json',
                    'content-Type' => 'application/json',
                    'x-api-key' => config('iam-api.key'),
                    'x-current-user' => Id::create(config('app.system_user_id'))->getUuid()
                ],
            ];

            $client = new Client($config);

            return new IamApiClient($client);
        });
        $this->app->singleton(GatewayIamApiClientInterface::class, function () {
            $host = rtrim(config('five-star-api.host'), '/') . '/api/user-service/v1/core-platform/';

            $config = [
                'verify' => config('app.verify_service_ssl'),
                'base_uri' => $host,
                'headers' => [
                    'accept' => 'application/json',
                    'content-Type' => 'application/json',
                    'x-api-key' => config('five-star-api.key'),
                    'x-sso-user-id' => Sxope::getCurrentUserId()->getUuid()
                ],
            ];

            $client = new Client($config);

            return new IamApiClient($client);
        });
    }

    public function registerGatewayApi(): void
    {
        $this->app->singleton(GatewayApiClient::class, static function () {
            $configuration = Configuration::getDefaultConfiguration()
                ->setApiKey('X-API-KEY', config('five-star-api.key'))
                ->setApiKey('X-SSO-USER-ID', config('app.system_user_id'))
                ->setHost(rtrim(config('five-star-api.host'), '/') . '/api');

            $client = new Client(
                [
                    'headers' => [
                        'accept' => 'application/json',
                        'content-Type' => 'application/json',
                    ],
                    'verify' => config('app.verify_service_ssl'),
                    'curl' => [
                        CURLOPT_FRESH_CONNECT => true,
                    ],
                ]
            );

            return new GatewayApiClient($client, $configuration);
        });
    }
}
