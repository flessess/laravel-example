<?php

declare(strict_types=1);

namespace App\Repository\MasterOutbox;

use App\Models\MasterOutbox\FileAllowedEntity;
use Illuminate\Database\Eloquent\Model;
use Sxope\Repositories\Repository;

/**
 * @extends Repository<FileAllowedEntity>
 */
class FileAllowedEntityRepository extends Repository
{
    public static function getEntityInstance(): Model
    {
        return new FileAllowedEntity();
    }
}
