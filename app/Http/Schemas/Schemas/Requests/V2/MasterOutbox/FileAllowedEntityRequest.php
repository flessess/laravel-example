<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Requests\V2\MasterOutbox;

use App\Enums\MasterOutbox\EntityTypes;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\UuidProperty;

class FileAllowedEntityRequest extends Schema
{
    public function __construct()
    {
        $allowedEntityTypes = array_map(
            static fn(EntityTypes $entity) => $entity->value . ' - ' . $entity->name,
            EntityTypes::cases()
        );

        parent::__construct(
            properties: [
                new Property(
                    property: 'allowed_entities',
                    type: 'array',
                    items: new Items(
                        properties: [
                            new Property
                            (
                                property: 'entity_type_id',
                                type: 'integer',
                                enum: $allowedEntityTypes,
                                example: 1
                            ),
                            new UuidProperty('entity_id')
                        ]
                    )
                ),
            ]
        );
    }
}
