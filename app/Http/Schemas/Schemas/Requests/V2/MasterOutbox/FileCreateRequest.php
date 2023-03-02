<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Requests\V2\MasterOutbox;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\DateProperty;
use Sxope\Http\Attributes\Properties\FileProperty;
use Sxope\Http\Attributes\Properties\IntegerProperty;
use Sxope\Http\Attributes\Properties\StringProperty;
use Sxope\Http\Attributes\Properties\UuidProperty;

class FileCreateRequest extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new FileProperty('file', 'Only jpeg,jpg,bmp,png,pdf,doc,img,docx,tif,tiff,rtf,csv,tsv,zip,txt,text files allowed with max size ~100MB'),
                new StringProperty('description'),
                new DateProperty('assigned_period'),
                new UuidProperty('card_id'),
                new IntegerProperty('use_card_permissions'),
            ]
        );
    }
}
