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
use Illuminate\Support\Facades\Facade;
use Sxope\ValueObjects\Id;

/**
 * @method static void setUserAllowedEntities(UserAllowedEntitiesDto $dto)
 * @method static void setPcpAllowedEntities(PcpAllowedEntitiesDto $dto)
 * @method static PcpAllowedEntitiesDto|null getPcpAllowedEntities(Id $pcpId)
 * @method static UserAllowedEntitiesDto|null getUserAllowedEntities(Id $id)
 * @method static Collection getPcpsAllowedEntities(array $pcpIds)
 * @method static void setUserGroup(Group $group)
 * @method static Group|null getUserGroup(Id $groupId)
 * @method static void setUserRole(Role $role)
 * @method static Role|null getUserRole(Id $roleId)
 * @method static void setPcpGroup(PcpGroup $group)
 * @method static PcpGroup|null getPcpGroup(Id $groupId)
 * @method static void setPayer(Payer $payer)
 * @method static Payer|null getPayer(Id $payerId)
 * @method static void setUser(UserData $userData)
 * @method static UserData|null getUser(Id $userId)
 */
class CacheAccessorServiceFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return CacheAccessorServiceInterface::class;
    }
}
