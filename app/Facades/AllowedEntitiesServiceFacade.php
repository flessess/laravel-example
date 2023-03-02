<?php

declare(strict_types=1);

namespace App\Facades;

use App\Enums\MasterOutbox\EntityTypes;
use App\Services\MasterOutbox\AllowedEntitiesService;
use Illuminate\Support\Facades\Facade;
use Sxope\ValueObjects\Id;

/**
 * @method static null|string getAllowedEntityName(EntityTypes $entityTypes, Id $entityId)
 */
class AllowedEntitiesServiceFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return AllowedEntitiesService::class;
    }
}
