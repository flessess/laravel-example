<?php

declare(strict_types=1);

namespace App\Api\Iam;

use App\Api\Iam\Contracts\IamApiClientInterface;
use App\Api\Iam\Dto\Users\UserData;
use App\Api\Iam\Responses\GetGroupsResponse;
use App\Api\Iam\Responses\GetRolesDataResponse;
use App\Api\Iam\Responses\GetUsersDataResponse;
use App\Api\Iam\Responses\GetUsersResponse;
use Sxope\ValueObjects\Id;

class IamApiDummyClient implements IamApiClientInterface
{
    public function __construct(private array $users = [])
    {
    }

    public function getUserData(Id $userId): ?UserData
    {
        return $this->users[$userId->getUuid()] ?? null;
    }

    public function getUsersData(array $userIds): GetUsersDataResponse
    {
        return new GetUsersDataResponse([]);
    }

    public function getGroups(): GetGroupsResponse
    {
        return new GetGroupsResponse([]);
    }

    public function getRoles(): GetRolesDataResponse
    {
        return new GetRolesDataResponse([]);
    }

    public function setUserData(UserData $data): void
    {
        $this->users[Id::create($data->userId)->getUuid()] = $data;
    }

    public function getUsers(): GetUsersResponse
    {
        return new GetUsersResponse($this->users);
    }
}
