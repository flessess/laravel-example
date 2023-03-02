<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Models\V1\Files;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\DateTimeProperty;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;

class FileMetadataModel extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: array_merge(
                [
                    new UuidProperty('file_id'),
                    new IntegerProperty('source'),
                    new StringProperty('sha256_hash'),
                    new IntegerProperty('crc32_checksum'),
                    new StringProperty('md5_checksum'),
                    new StringProperty('original_file_name'),
                    new StringProperty('mime_type'),
                    new IntegerProperty('file_size'),
                    new IntegerProperty('pages_count'),
                    new DateTimeProperty('created_time'),
                    new StringProperty('email'),
                    new UuidProperty('user_id'),
                    new StringProperty('email_subject'),
                    new StringProperty('sender_phone_number'),
                ],
            )
        );
    }
}
