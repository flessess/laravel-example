<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Models\V1;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\BooleanProperty;
use Sxope\Http\Attributes\Properties\Collections\CRUDInfoCollection;
use Sxope\Http\Attributes\Properties\DateProperty;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;

class MasterOutboxFileSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: array_merge(
                [
                    new UuidProperty('entity_id'),
                    new IntegerProperty('master_outbox_file_id'),
                    new UuidProperty('data_owner_id'),
                    new StringProperty('description'),
                    new IntegerProperty('master_outbox_file_visibility_type_id'),
                    new IntegerProperty('master_outbox_file_type_id'),
                    new IntegerProperty('master_outbox_file_entity_type_id'),
                    new DateProperty('assigned_period'),
                    new StringProperty('original_file_name', 'avatar.jpg'),
                    new IntegerProperty('file_size'),
                    new BooleanProperty('is_read'),
                ],
                CRUDInfoCollection::createdProperties(),
                CRUDInfoCollection::updatedProperties(),
            )
        );
    }
}
