<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Responses\V1;

use App\Http\Attributes\Schemas\Models\V1\MasterOutboxFileSchema;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\ObjectsArrayProperty;

class GetFilesDataResponseSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new IntegerProperty('current_page'),
                new IntegerProperty('from'),
                new IntegerProperty('last_page'),
                new IntegerProperty('per_page'),
                new IntegerProperty('to'),
                new IntegerProperty('total'),
                new ObjectsArrayProperty('data', MasterOutboxFileSchema::class)
            ]
        );
    }
}
