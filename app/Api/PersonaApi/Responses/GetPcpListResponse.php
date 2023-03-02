<?php

declare(strict_types=1);

namespace App\Api\PersonaApi\Responses;

use App\Api\PersonaApi\Models\Pcp;
use Sxope\Api\ApiResponse;

class GetPcpListResponse extends ApiResponse
{
    /**
     * @var Pcp[]
     */
    public mixed $data;
}
