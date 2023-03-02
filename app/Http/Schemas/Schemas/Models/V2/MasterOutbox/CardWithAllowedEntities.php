<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Models\V2\MasterOutbox;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\BooleanProperty;
use Sxope\Http\Attributes\Properties\Collections\CRUDInfoCollection;
use Sxope\Http\Attributes\Properties\ObjectsArrayProperty;
use Sxope\Http\Attributes\Properties\StringProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;

class CardWithAllowedEntities extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: array_merge(
                [
                    new UuidProperty('card_id'),
                    new StringProperty('card_name', 'Name'),
                    new StringProperty('logo', 'base64'),
                    new BooleanProperty('is_custom'),
                    new BooleanProperty('show_on_dashboard'),
                    new ObjectsArrayProperty('allowed_entities', CardAllowedEntity::class),
                ],
                CRUDInfoCollection::createdProperties(),
                CRUDInfoCollection::updatedProperties()
            )
        );
    }
}
