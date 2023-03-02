<?php

declare(strict_types=1);

namespace App\Api\DataApi\Contracts;

use App\Api\DataApi\Responses\GetPcpsDataResponse;

interface GatewayDataApiClientInterface
{
    public function getPcpsData(array $pcps = null): GetPcpsDataResponse;
}
