<?php

declare(strict_types=1);

namespace App\Api\PersonaApi\Contracts;

use App\Api\PersonaApi\Models\PcpGroup;
use Sxope\Api\ApiResponse;

interface GatewayPersonaApiClientInterface
{
    public function getPcpGroups(string $search = null): ApiResponse;
    public function getPcpGroupByName(string $name): ?PcpGroup;
}
