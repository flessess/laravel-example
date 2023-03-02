<?php

declare(strict_types=1);

namespace App\Models\MasterOutbox;

use App\Models\BaseModel;

class EntityType extends BaseModel
{
    public $table = 'mo_entity_types';
    public $primaryKey = 'entity_type_id';
    public $timestamps = false;
    protected $guarded = [];
}
