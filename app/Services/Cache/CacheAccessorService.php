<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Api\Iam\Dto\Groups\Group;
use App\Api\Iam\Dto\Roles\Role;
use App\Api\Iam\Dto\Users\UserData;
use App\Api\PersonaApi\Models\PcpGroup;
use App\Api\SphereApi\Dto\Payer;
use App\Services\MasterOutbox\Dto\PcpAllowedEntitiesDto;
use App\Services\MasterOutbox\Dto\UserAllowedEntitiesDto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Sxope\ValueObjects\Id;

class CacheAccessorService implements CacheAccessorServiceInterface
{
    public const USER_ALLOWED_ENTITIES_TTL = 3600;
    public const FOLDERS_CACHE_TAG = 'folder_cache_tag';
    public const PCP_DATA_CACHE_TAG = 'pcp_data_cache_tag';
    public const USER_GROUPS_CACHE_TAG = 'user_group_cache_tag';
    public const USER_ROLES_CACHE_TAG = 'user_role_cache_tag';
    public const PCP_GROUPS_CACHE_TAG = 'pcp_group_cache_tag';
    public const PAYERS_CACHE_TAG = 'payer_cache_tag';
    public const USERS_CACHE_TAG = 'user_cache_tag';
    public const USERS_ALLOWED_ENTITIES_TAG = 'user_allowed_entities';

    public function setUserAllowedEntities(UserAllowedEntitiesDto $dto): void
    {
        Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::USERS_ALLOWED_ENTITIES_TAG,
        ])->put(
            'user_allowed_entities_' . Id::create($dto->userId)->getHex(),
            $dto,
            self::USER_ALLOWED_ENTITIES_TTL
        );
    }

    public function getUserAllowedEntities(Id $userId): ?UserAllowedEntitiesDto
    {
        return Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::USERS_ALLOWED_ENTITIES_TAG,
        ])->get('user_allowed_entities_' . $userId->getHex());
    }

    public function setPcpAllowedEntities(PcpAllowedEntitiesDto $dto): void
    {
        Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::PCP_DATA_CACHE_TAG,
        ])->put('pcp_allowed_entities_' . Id::create($dto->pcpId)->getHex(), $dto);
    }

    public function getPcpAllowedEntities(Id $pcpId): ?PcpAllowedEntitiesDto
    {
        return Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::PCP_DATA_CACHE_TAG,
        ])->get('pcp_allowed_entities_' . $pcpId->getHex());
    }

    public function getPcpsAllowedEntities(array $pcpIds): Collection
    {
        $pcpDataKeys = array_map(static fn(string $id) => 'pcp_allowed_entities_' . Id::create($id)->getHex(), $pcpIds);
        return collect(
            Cache::tags([
                self::FOLDERS_CACHE_TAG,
                self::PCP_DATA_CACHE_TAG,
            ])
                ->many(array_values($pcpDataKeys)) ?? []
        );
    }

    public function setUserGroup(Group $group): void
    {
        $groupId = Id::create($group->userGroupId);
        Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::USER_GROUPS_CACHE_TAG,
        ])->set('user_group_' . $groupId->getHex(), $group);
    }

    public function getUserGroup(Id $groupId): ?Group
    {
        return Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::USER_GROUPS_CACHE_TAG,
        ])->get('user_group_' . $groupId->getHex());
    }

    public function setUserRole(Role $role): void
    {
        $roleId = Id::create($role->roleId);
        Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::USER_ROLES_CACHE_TAG,
        ])->set('user_role_' . $roleId->getHex(), $role);
    }

    public function getUserRole(Id $roleId): ?Role
    {
        return Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::USER_ROLES_CACHE_TAG,
        ])->get('user_role_' . $roleId->getHex());
    }

    public function setPcpGroup(PcpGroup $group): void
    {
        $groupId = Id::create($group->entityId);
        Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::PCP_GROUPS_CACHE_TAG,
        ])->set('pcp_group_' . $groupId->getHex(), $group);
    }

    public function getPcpGroup(Id $groupId): ?PcpGroup
    {
        return Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::PCP_GROUPS_CACHE_TAG,
        ])->get('pcp_group_' . $groupId->getHex());
    }

    public function setPayer(Payer $payer): void
    {
        $payerId = Id::create($payer->payerId);
        Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::PAYERS_CACHE_TAG,
        ])->set('payer_' . $payerId->getHex(), $payer);
    }

    public function getPayer(Id $payerId): ?Payer
    {
        return Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::PAYERS_CACHE_TAG,
        ])->get('payer_' . $payerId->getHex());
    }

    public function setUser(UserData $userData): void
    {
        $userId = Id::create($userData->userId);
        Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::USERS_CACHE_TAG
        ])->set('user_' . $userId->getHex(), $userData);
    }

    public function getUser(Id $userId): ?UserData
    {
        return Cache::tags([
            self::FOLDERS_CACHE_TAG,
            self::USERS_CACHE_TAG
        ])->get('user_' . $userId->getHex());
    }
}
