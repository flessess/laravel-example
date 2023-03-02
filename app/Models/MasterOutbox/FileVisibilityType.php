<?php

declare(strict_types=1);

namespace App\Models\MasterOutbox;

use App\Models\BaseModel;

/**
 * @property integer $visibility_type_id
 * @property string $visibility_type_name
 */
class FileVisibilityType extends BaseModel
{
    public const TYPE_PUBLIC_ID = 1;
    public const TYPE_PRIVATE_ID = 2;
    public const TYPE_NAMES = [
        self::TYPE_PUBLIC_ID => 'PUBLIC',
        self::TYPE_PRIVATE_ID => 'PRIVATE',
    ];

    public $table = 'mo_file_visibility_type';
    public $primaryKey = 'visibility_type_id';
    public $timestamps = false;
    protected $guarded = [];
}
