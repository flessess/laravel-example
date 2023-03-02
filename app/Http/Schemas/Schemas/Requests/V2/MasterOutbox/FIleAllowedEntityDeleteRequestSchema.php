<?php

namespace App\Http\Attributes\Schemas\Requests\V2\MasterOutbox;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

class FIleAllowedEntityDeleteRequestSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new Property(
                    property: 'file_allowed_entity_id',
                    type: 'array',
                    items: new Items(
                        type: 'string',
                        example: 'd46ea2b3-e5ad-4fd7-8b0f-d5ebb784bb00',
                    )
                )
            ]
        );
    }
}
