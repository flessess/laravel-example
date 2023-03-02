<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Responses\V1;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\DateTimeProperty;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;

class UploadedFile extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new UuidProperty('file_id'),
                new UuidProperty('data_owner_id'),
                new StringProperty('original_file_name', 'PAYER'),
                new IntegerProperty('file_size', 1234),
                new IntegerProperty('pages_count', 12),
                new DateTimeProperty('uploaded_at'),
                new UuidProperty('uploaded_by'),
                new UuidProperty('known_source_id'),
                new UuidProperty('file_source_id'),
                new UuidProperty('mime_type_id'),
                new UuidProperty('file_status_id'),
            ]
        );
    }
}
