<?php

declare(strict_types=1);

namespace App\Api\Iam\Facades;

use App\Api\Iam\Contracts\IamApiClientInterface;
use App\Api\Iam\Dto\Users\UserData;
use App\Api\Iam\Responses\GetGroupsResponse;
use App\Api\Iam\Responses\GetRolesDataResponse;
use App\Api\Iam\Responses\GetUsersResponse;
use Illuminate\Support\Facades\Facade;
use Sxope\ValueObjects\Id;

/**
 * @method static UserData getUserData(Id $userId)
 * @method static GetGroupsResponse getGroups()
 * @method static GetRolesDataResponse getRoles()
 * @method static GetUsersResponse getUsers()
 */
class IamApiClientFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return IamApiClientInterface::class;
    }
}
