<?php

declare(strict_types=1);

namespace App\Api\DataApi\Facades;

use App\Api\DataApi\Contracts\DataApiClientInterface;
use App\Api\DataApi\Dto\PcpDataDto;
use App\Api\DataApi\Responses\GetPcpsDataResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static GetPcpsDataResponse getPcpsData(array $pcps = null)
 * @method static PcpDataDto|null getPcpData(string $pcpId)
 */
class DataApiClientFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return DataApiClientInterface::class;
    }
}
