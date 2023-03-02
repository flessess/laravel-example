<?php

declare(strict_types=1);

namespace App\Models\MasterOutbox;

use App\Models\File;
use App\Models\SpannerModel;
use App\Models\Traits\Only;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * @property Bytes $card_id
 * @property string $card_name
 * @property string $logo
 * @property boolean $is_custom
 * @property boolean $show_on_dashboard
 * @property Collection $allowed
 * @property Bytes $created_at_day_id
 * @property Carbon $created_at
 * @property Bytes $created_by
 * @property Carbon $updated_at
 * @property Bytes $updated_by
 * @property Bytes $deleted_at_day_id
 * @property Carbon $deleted_at
 * @property Bytes $deleted_by
 */
class Card extends SpannerModel
{
    use SoftDeletes, Only, HasFactory;

    public $table = 'mo_cards';
    public $primaryKey = 'card_id';
    public $timestamps = false;
    protected $guarded = [];

    public static array $onlyFields = [
        'card_id',
        'card_name',
        'logo',
        'is_custom',
        'show_on_dashboard',
        'created_at',
        'created_by_name',
        'created_by',
        'updated_at',
        'updated_by_name',
        'updated_by',
    ];

    protected $casts = [
        'card_id' => 'spanner_binary_uuid',
        'created_by' => 'spanner_binary_uuid',
        'updated_by' => 'spanner_binary_uuid',
        'deleted_by' => 'spanner_binary_uuid',
    ];

    public function getLogoAttribute(?string $logo): ?string
    {
        if ($logo !== null) {
            $content = Storage::disk(File::UPLOADS_DISK)
                ->get($logo);
            $logo = $content !== null ? base64_encode($content) : null;
        }

        return $logo;
    }

    public function allowedEntities(): HasMany
    {
        return $this->hasMany(CardAllowedEntity::class, 'card_id', 'card_id');
    }
}
