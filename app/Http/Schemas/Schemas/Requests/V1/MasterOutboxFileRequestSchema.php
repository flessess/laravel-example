<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Requests\V1;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\DateProperty;
use Sxope\Http\Attributes\Properties\FileProperty;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;

class MasterOutboxFileRequestSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new UuidProperty('entity_id'),
                new IntegerProperty('master_outbox_file_type_id'),
                new IntegerProperty('master_outbox_file_entity_type_id'),
                new IntegerProperty('master_outbox_file_visibility_type_id'),
                new DateProperty('assigned_period'),
                new StringProperty('description'),
                new FileProperty('attachment', 'Only PDF files allowed with max size ~50MB'),
            ]
        );
    }
}
