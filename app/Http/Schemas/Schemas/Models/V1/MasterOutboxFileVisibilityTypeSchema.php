<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Models\V1;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;

class MasterOutboxFileVisibilityTypeSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new IntegerProperty('master_outbox_file_visibility_type_id'),
                new StringProperty('master_outbox_file_visibility_type_name', 'PUBLIC')
            ]
        );
    }
}
