<?php

declare(strict_types=1);

namespace App\Api\GatewayApi;

use GuzzleHttp\Client;
use Inventcorp\SxopeApiGatewayClient\Api\SpherePayersApi;
use Inventcorp\SxopeApiGatewayClient\Configuration;
use Inventcorp\SxopeApiGatewayClient\Model\InlineResponse200712;
use Inventcorp\SxopeApiGatewayClient\Model\ModelInterface;

class GatewayApiClient
{
    private array $clients;

    public function __construct(private readonly Client $client, private readonly Configuration $configuration)
    {
    }

    /**
     * @return InlineResponse200712
     */
    public function getPayers(int $perPage = 200): ModelInterface
    {
        return $this->buildGatewayClient(SpherePayersApi::class)->sphereV2listPayers(perPage: 200);
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return T
     */
    private function buildGatewayClient(string $class)
    {
        if (!isset($this->clients[$class])) {
            $this->clients[$class] = new $class($this->client, $this->configuration);
        }

        return $this->clients[$class];
    }
}
