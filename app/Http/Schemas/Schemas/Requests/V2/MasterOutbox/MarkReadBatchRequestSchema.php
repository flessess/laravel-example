<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Requests\V2\MasterOutbox;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\BooleanProperty;

class MarkReadBatchRequestSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new BooleanProperty('is_read'),
                new Property(
                    property: 'file_ids',
                    type: 'array',
                    items: new Items(
                        type: 'string',
                        example: 'b4f67f6-a5dd-4bc4-9182-9e4d90976840'
                    )
                )
            ]
        );
    }

}
