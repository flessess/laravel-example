<?php

namespace App\Api\DataApi\Facades;

use App\Api\DataApi\Contracts\GatewayDataApiClientInterface;
use App\Api\DataApi\Responses\GetPcpsDataResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static GetPcpsDataResponse getPcpsData(array $pcps = null);
 */
class GatewayDataApiClientFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return GatewayDataApiClientInterface::class;
    }
}
