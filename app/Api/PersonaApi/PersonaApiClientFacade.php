<?php

declare(strict_types=1);

namespace App\Api\PersonaApi;

use App\Api\PersonaApi\Contracts\PersonaApiClientInterface;
use App\Api\PersonaApi\Models\PcpGroup;
use App\Api\PersonaApi\Responses\FindPcpByContactResponse;
use App\Api\PersonaApi\Responses\GetPcpGroupListResponse;
use App\Api\PersonaApi\Responses\GetPcpListResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static GetPcpGroupListResponse getPcpGroups(string $search = null)
 * @method static PcpGroup|null getPcpGroupByName(string $name)
 * @method static FindPcpByContactResponse findByContact(mixed $term)
 * @method static GetPcpListResponse getPcpList()
 */
class PersonaApiClientFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return PersonaApiClientInterface::class;
    }
}
