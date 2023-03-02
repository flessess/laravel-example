<?php

declare(strict_types=1);

namespace App\Api\Iam\Responses;

use App\Api\Iam\Dto\Groups\Group;
use Sxope\Api\ApiResponse;

class GetGroupsResponse extends ApiResponse
{
    /**
     * @var Group[]
     */
    public mixed $data;
}
