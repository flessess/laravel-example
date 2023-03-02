<?php

declare(strict_types=1);

namespace App\Api\DataApi\Contracts;

use App\Api\DataApi\Dto\PcpDataDto;
use App\Api\DataApi\Responses\GetPcpsDataResponse;

interface DataApiClientInterface
{
    public function getPcpData(string $pcpId): ?PcpDataDto;
    public function getPcpsData(array $pcps = null): GetPcpsDataResponse;
}
