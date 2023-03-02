<?php

declare(strict_types=1);

namespace App\Api\Iam\Contracts;

use App\Api\Iam\Dto\Users\UserData;
use App\Api\Iam\Responses\GetGroupsResponse;
use App\Api\Iam\Responses\GetRolesDataResponse;
use App\Api\Iam\Responses\GetUsersResponse;
use Sxope\ValueObjects\Id;

interface IamApiClientInterface
{
    public function getUserData(Id $userId): ?UserData;
    public function getGroups(): GetGroupsResponse;
    public function getRoles(): GetRolesDataResponse;
    public function getUsers(): GetUsersResponse;
}
