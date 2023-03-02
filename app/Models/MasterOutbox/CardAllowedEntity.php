<?php

declare(strict_types=1);

namespace App\Models\MasterOutbox;

use App\Enums\MasterOutbox\EntityTypes;
use App\Facades\AllowedEntitiesServiceFacade;
use App\Models\SpannerModel;
use App\Models\Traits\Only;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Sxope\ValueObjects\Id;

/**
 * @property Bytes $card_id
 * @property int $entity_type_id
 * @property Bytes $entity_id
 * @property string $entity_name
 * @property Bytes $card_allowed_entity_id
 * @property Bytes $created_at_day_id
 * @property Carbon $created_at
 * @property Bytes $created_by
 * @property Carbon $updated_at
 * @property Bytes $updated_by
 * @property Bytes $deleted_at_day_id
 * @property Carbon $deleted_at
 * @property Bytes $deleted_by
 */
class CardAllowedEntity extends SpannerModel
{
    use SoftDeletes, Only, HasFactory;

    public static array $onlyFields = [
        'card_id',
        'entity_type_id',
        'entity_id',
        'entity_name',
        'data_owner_id',
        'card_allowed_entity_id',
        'created_at',
        'created_by_name',
        'created_by',
    ];

    public $table = 'mo_card_allowed_entities';
    protected $primaryKey = 'card_allowed_entity_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'card_allowed_entity_id' => 'spanner_binary_uuid',
        'card_id' => 'spanner_binary_uuid',
        'entity_id' => 'spanner_binary_uuid',
        'created_by' => 'spanner_binary_uuid',
        'updated_by' => 'spanner_binary_uuid',
        'deleted_by' => 'spanner_binary_uuid',
    ];

    public function getEntityNameAttribute(): ?string
    {
        return AllowedEntitiesServiceFacade::getAllowedEntityName(
            EntityTypes::tryFrom($this->entity_type_id),
            Id::create($this->entity_id)
        );
    }
}
