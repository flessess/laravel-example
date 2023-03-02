<?php

declare(strict_types=1);

namespace App\Repository\MasterOutbox;

use App\Models\MasterOutbox\FileViewLog;
use Illuminate\Database\Eloquent\Model;
use Sxope\Repositories\Repository;

/**
 * @extends Repository<FileViewLog>
 */
class FileViewLogRepository extends Repository
{
    public static function getEntityInstance(): Model
    {
        return new FileViewLog();
    }
}
