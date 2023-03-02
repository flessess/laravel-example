<?php

declare(strict_types=1);

namespace App\Api\PersonaApi\Responses;

use Sxope\Api\ApiResponse;
use App\Api\PersonaApi\Models\PcpGroup;

class GetPcpGroupListResponse extends ApiResponse
{
    /**
     * @var PcpGroup[]
     */
    public mixed $data;
}
