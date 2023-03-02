<?php

namespace App\Api\DataApi;

use App\Api\DataApi\Contracts\DataApiClientInterface;
use App\Api\DataApi\Contracts\GatewayDataApiClientInterface;
use App\Api\DataApi\Dto\PcpDataDto;
use App\Api\DataApi\Responses\GetPcpsDataResponse;
use Sxope\ValueObjects\Id;

class DataApiDummyClient implements DataApiClientInterface, GatewayDataApiClientInterface
{
    /**
     * @var PcpDataDto[]
     */
    private array $pcps = [];

    public function getPcpData(string $pcpId): ?PcpDataDto
    {
        return $this->pcps[Id::create($pcpId)->getHex()] ?? null;
    }

    public function getPcpsData(array $pcps = null): GetPcpsDataResponse
    {
        return new GetPcpsDataResponse($this->pcps);
    }

    public function addPcp(PcpDataDto $pcp): void
    {
        $this->pcps[$pcp->pcpId] = $pcp;
    }
}
