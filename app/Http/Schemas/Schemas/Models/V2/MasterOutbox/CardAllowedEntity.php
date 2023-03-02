<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Models\V2\MasterOutbox;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\Collections\CRUDInfoCollection;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;

class CardAllowedEntity extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: array_merge(
                [
                    new UuidProperty('card_id'),
                    new IntegerProperty('entity_type_id'),
                    new StringProperty('entity_name'),
                    new UuidProperty('entity_id'),
                    new UuidProperty('data_owner_id'),
                    new UuidProperty('card_allowed_entity_id'),
                ],
                CRUDInfoCollection::createdProperties(),
                CRUDInfoCollection::updatedProperties()
            )
        );
    }
}
