<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Requests\V2\MasterOutbox;

use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\ObjectsArrayProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;

class FileUpdateRequest extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new UuidProperty('card_id'),
                new IntegerProperty('use_card_permissions'),
                new ObjectsArrayProperty(
                    'allowed_entities',
                    [
                        new Property(property: 'entity_type_id', type: 'integer', example: 1),
                        new UuidProperty('entity_id'),
                    ]
                )
            ]
        );
    }
}
