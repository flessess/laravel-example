<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Models\V1;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\DateProperty;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;

class MasterOutboxFilesAggregatedSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new UuidProperty('entity_id'),
                new IntegerProperty('total_files'),
                new IntegerProperty('total_read_files'),
                new DateProperty('updated_at'),
            ]
        );
    }
}
