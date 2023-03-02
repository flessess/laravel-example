<?php

declare(strict_types=1);

namespace App\Models\MasterOutbox;

use App\Models\BaseModel;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Support\Carbon;

/**
 * @property Bytes $mo_file_view_logs
 * @property Bytes $file_id
 * @property Bytes $data_owner_id
 * @property Bytes $user_id
 * @property Bytes $is_read
 * @property Bytes $created_at_day_id
 * @property Carbon $created_at
 * @property Bytes $created_by
 * @property Carbon $updated_at
 * @property Bytes $updated_by
 */
class FileViewLog extends BaseModel
{
    public $table = 'mo_file_view_logs';
    public $primaryKey = 'file_view_log_id';
    public $timestamps = false;
    protected $guarded = [];
}
