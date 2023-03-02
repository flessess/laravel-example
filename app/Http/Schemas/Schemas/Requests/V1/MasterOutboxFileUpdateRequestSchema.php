<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Requests\V1;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\DateProperty;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;

class MasterOutboxFileUpdateRequestSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new IntegerProperty('master_outbox_file_type_id'),
                new IntegerProperty('master_outbox_file_visibility_type_id'),
                new DateProperty('assigned_period'),
                new StringProperty('description'),
            ]
        );
    }
}
