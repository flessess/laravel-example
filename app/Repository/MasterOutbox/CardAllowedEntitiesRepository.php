<?php

declare(strict_types=1);

namespace App\Repository\MasterOutbox;

use App\Models\MasterOutbox\CardAllowedEntity;
use Illuminate\Database\Eloquent\Model;
use Sxope\Repositories\Repository;

/**
 * @extends Repository<CardAllowedEntity>
 */
class CardAllowedEntitiesRepository extends Repository
{
    public static function getEntityInstance(): Model
    {
        return new CardAllowedEntity();
    }
}
