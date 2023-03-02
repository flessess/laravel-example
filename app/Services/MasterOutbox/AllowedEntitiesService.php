<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox;

use App\Enums\MasterOutbox\EntityTypes;
use App\Services\Cache\CacheAccessorServiceFacade;
use App\Services\Cache\CacheService;
use App\Services\MasterOutbox\Dto\UserAllowedEntitiesDto;
use Sxope\ValueObjects\Id;

class AllowedEntitiesService
{
    public function __construct(private readonly CacheService $cacheService)
    {
    }

    public function getUserAllowedEntities(Id $userId): UserAllowedEntitiesDto
    {
        return $this->cacheService->getUserEntitiesData($userId);
    }

    public function getAllowedEntityName(EntityTypes $entityTypes, Id $entityId): ?string
    {
        return match ($entityTypes) {
            EntityTypes::PCP => CacheAccessorServiceFacade::getPcpAllowedEntities($entityId)?->pcpName,
            EntityTypes::USER_GROUP => CacheAccessorServiceFacade::getUserGroup($entityId)?->name,
            EntityTypes::USER_ROLE => CacheAccessorServiceFacade::getUserRole($entityId)?->name,
            EntityTypes::PCP_GROUP => CacheAccessorServiceFacade::getPcpGroup($entityId)?->pcpGroupName,
            EntityTypes::PAYER => CacheAccessorServiceFacade::getPayer($entityId)?->payerName,
            EntityTypes::USER => CacheAccessorServiceFacade::getUser($entityId)?->getFullName(),
        };
    }
}
