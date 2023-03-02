<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Api\Iam\Dto\Groups\Group;
use App\Api\Iam\Dto\Roles\Role;
use App\Api\Iam\Dto\Users\UserData;
use App\Api\PersonaApi\Models\PcpGroup;
use App\Api\SphereApi\Dto\Payer;
use App\Services\MasterOutbox\Dto\PcpAllowedEntitiesDto;
use Illuminate\Support\Collection;
use Sxope\ValueObjects\Id;

interface CacheAccessorServiceInterface
{
    public function setPcpAllowedEntities(PcpAllowedEntitiesDto $dto): void;
    public function getPcpAllowedEntities(Id $pcpId): ?PcpAllowedEntitiesDto;
    public function getPcpsAllowedEntities(array $pcpIds): Collection;
    public function setUserGroup(Group $group): void;
    public function getUserGroup(Id $groupId): ?Group;
    public function setUserRole(Role $role): void;
    public function getUserRole(Id $roleId): ?Role;
    public function setPcpGroup(PcpGroup $group): void;
    public function getPcpGroup(Id $groupId): ?PcpGroup;
    public function setPayer(Payer $payer): void;
    public function getPayer(Id $payerId): ?Payer;
    public function setUser(UserData $userData): void;
    public function getUser(Id $userId): ?UserData;
}
