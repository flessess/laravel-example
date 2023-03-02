<?php

declare(strict_types=1);

namespace App\Api\Iam\Responses;

use App\Api\Iam\Dto\Users\UserData;
use Sxope\Api\ApiResponse;

class GetUsersDataResponse extends ApiResponse
{
    /**
     * @var UserData[]
     */
    public mixed $data;
}
