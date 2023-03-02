<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Models\V2\MasterOutbox;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\BooleanProperty;
use Sxope\Http\Attributes\Properties\Collections\CRUDInfoCollection;
use Sxope\Http\Attributes\Properties\DateProperty;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;

class FileEntity extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: array_merge(
                [
                    new UuidProperty('file_id'),
                    new UuidProperty('data_owner_id'),
                    new StringProperty('description'),
                    new DateProperty('assigned_period'),
                    new IntegerProperty('visibility_type_id'),
                    new UuidProperty('card_id'),
                    new BooleanProperty('use_card_permissions'),
                    new StringProperty('original_file_name', 'files/file.pdf'),
                    new IntegerProperty('file_size', 50000),
                    new StringProperty('md5_checksum', 'd46ea2b3e5ad4fd78b0fd5ebb784bb00'),
                ],
                CRUDInfoCollection::createdProperties(),
                CRUDInfoCollection::updatedProperties()
            )
        );
    }
}
