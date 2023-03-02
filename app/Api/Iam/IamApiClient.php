<?php

declare(strict_types=1);

namespace App\Api\Iam;

use App\Api\Iam\Contracts\GatewayIamApiClientInterface;
use App\Api\Iam\Contracts\IamApiClientInterface;
use App\Api\Iam\Dto\Groups\Group;
use App\Api\Iam\Dto\Roles\Role;
use App\Api\Iam\Dto\Users\UserData;
use App\Api\Iam\Dto\Users\UserGroup;
use App\Api\Iam\Dto\Users\UserRole;
use App\Api\Iam\Responses\GetGroupsResponse;
use App\Api\Iam\Responses\GetRolesDataResponse;
use App\Api\Iam\Responses\GetUsersDataResponse;
use App\Api\Iam\Responses\GetUsersResponse;
use Sxope\Api\ApiCachedClient;
use Sxope\Services\Serializer\Elements\ArrayItem;
use Sxope\ValueObjects\Id;
use Throwable;

class IamApiClient extends ApiCachedClient implements IamApiClientInterface, GatewayIamApiClientInterface
{
    public function getUserData(Id $userId): ?UserData
    {
        try {
            return $this->getUsersData([$userId->getUuid()])->data[0] ?? null;
        } catch (Throwable) {
            return null;
        }
    }

    public function getUsersData(array $userIds = null): GetUsersDataResponse
    {
        $availableFields = [
            'user_id',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'groups',
            'roles',
        ];

        $filters = [];
        if ($userIds !== null) {
            $filters = [
                'user_id' => [
                    'contains' => $userIds,
                ],
            ];
        }

        return $this->walkPages(
            function ($page, $limit) use ($filters, $availableFields) {
                $offset = $page * $limit;

                return $this->handleResponse(
                    $this->post(
                        'v2/users/',
                        [
                            'json' => [
                                'available_fields' => $availableFields,
                                'filter' => $filters,
                            ],
                            'query' => ['limit' => $limit, 'offset' => $offset],
                        ],
                    ),
                    GetUsersDataResponse::class,
                    [
                        ArrayItem::create(
                            UserData::class,
                            [
                                'roles' => [UserRole::class],
                                'groups' => [UserGroup::class],
                            ]
                        ),
                    ],
                    ['sessionLifetime']
                );
            },
            0
        );
    }

    public function getGroups(): GetGroupsResponse
    {
        return $this->walkPages(
            function ($page, $limit) {
                $offset = $page * $limit;
                return $this->handleResponse(
                    $this->post(
                        'v2/groups/',
                        [
                            'json' => [
                                'available_fields' => [
                                    'user_group_id',
                                    'name',
                                    'description',
                                    'system',
                                    'count_users'
                                ]
                            ],
                            'query' => ['limit' => $limit, 'offset' => $offset],
                        ],
                    ),
                    GetGroupsResponse::class,
                    [ArrayItem::create(Group::class)],
                    ['system', 'countUsers']
                );
            },
            page: 0
        );
    }

    public function getRoles(): GetRolesDataResponse
    {
        return $this->walkPages(
            function ($page, $limit) {
                $offset = $page * $limit;
                return $this->handleResponse(
                    $this->post(
                        'v2/roles/',
                        [
                            'json' => [
                                'available_fields' => [
                                    'page_access_scheme_id',
                                    'name',
                                    'description',
                                    'system'
                                ]
                            ],
                            'query' => ['limit' => $limit, 'offset' => $offset],
                        ],
                    ),
                    GetRolesDataResponse::class,
                    [
                        ArrayItem::create(Role::class, ['pageAccessSchemeId' => 'roleId']),
                    ],
                    ['system']
                );
            },
            page: 0
        );
    }

    public function getUsers(): GetUsersResponse
    {
        return $this->walkPages(
            function ($page, $limit) {
                $offset = $page * $limit;
                return $this->handleResponse(
                    $this->get(
                        'v1/users/',
                        [
                            'query' => ['page' => $page, 'per_page' => $offset],
                        ],
                    ),
                    GetUsersResponse::class,
                    [
                        ArrayItem::create(UserData::class),
                    ],
                    ['isActive']
                );
            },
        );

    }

    public static function getServiceCommonCacheTag(): string
    {
        return 'iam';
    }
}
