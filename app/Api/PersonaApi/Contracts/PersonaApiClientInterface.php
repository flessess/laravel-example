<?php

declare(strict_types=1);

namespace App\Api\PersonaApi\Contracts;

use App\Api\PersonaApi\Models\PcpGroup;
use App\Api\PersonaApi\Responses\FindPcpByContactResponse;
use App\Api\PersonaApi\Responses\GetPcpGroupListResponse;
use App\Api\PersonaApi\Responses\GetPcpListResponse;

interface PersonaApiClientInterface
{
    public function getPcpGroups(string $search = null): GetPcpGroupListResponse;
    public function getPcpGroupByName(string $name): ?PcpGroup;
    public function findByContact($term): FindPcpByContactResponse;
    public function getPcpList(): GetPcpListResponse;
}
