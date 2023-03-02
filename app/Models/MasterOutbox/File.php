<?php

declare(strict_types=1);

namespace App\Models\MasterOutbox;

use App\Models\BaseModel;
use App\Models\Traits\Only;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Bytes $file_id
 * @property Bytes $data_owner_id
 * @property string $description
 * @property Carbon $assigned_period
 * @property integer $visibility_type_id
 * @property Bytes $card_id
 * @property boolean $use_card_permissions
 * @property string $original_file_name
 * @property integer $file_size
 * @property Bytes $md5_checksum
 * @property FileAllowedEntity[] $allowedEntities
 * @property Bytes $created_at_day_id
 * @property Carbon $created_at
 * @property Bytes $created_by
 * @property Carbon $updated_at
 * @property Bytes $updated_by
 * @property Bytes $deleted_at_day_id
 * @property Carbon $deleted_at
 * @property Bytes $deleted_by
 */
class File extends BaseModel
{
    use SoftDeletes, Only, HasFactory;

    public $table = 'mo_files';
    public $primaryKey = 'file_id';
    public $timestamps = false;
    protected $guarded = [];

    public static array $onlyFields = [
        'file_id',
        'data_owner_id',
        'description',
        'assigned_period',
        'visibility_type_id',
        'card_id',
        'use_card_permissions',
        'original_file_name',
        'file_size',
        'md5_checksum',
        'created_at',
        'created_by',
        'created_by_name',
        'updated_at',
        'updated_by',
        'updated_by_name',
    ];

    protected $casts = [
        'card_id' => 'spanner_binary_uuid',
        'file_id' => 'spanner_binary_uuid',
        'assigned_period' => 'date:Y-m-d',
    ];

    public function allowedEntities(): HasMany
    {
        return $this->hasMany(FileAllowedEntity::class, 'file_id', 'file_id');
    }
}
