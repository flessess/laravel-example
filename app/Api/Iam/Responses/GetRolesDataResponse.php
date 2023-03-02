<?php

declare(strict_types=1);

namespace App\Api\Iam\Responses;

use App\Api\Iam\Dto\Roles\Role;
use Sxope\Api\ApiResponse;

class GetRolesDataResponse extends ApiResponse
{
    /**
     * @var Role[]
     */
    public mixed $data;
}
