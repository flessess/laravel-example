<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Models\V2\MasterOutbox;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;

class DictionaryModel extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new Property(
                    property: 'entity_type',
                    type: 'array',
                    items: new Items(
                        properties: [
                            new IntegerProperty('entity_type_id'),
                            new StringProperty('entity_type_name'),
                        ],
                        type: 'object'
                    )
                )
            ]
        );
    }
}
