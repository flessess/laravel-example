<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Models\V2\MasterOutbox;

use Sxope\Http\Attributes\Properties\ObjectsArrayProperty;

class FileEntityWithAllowed extends FileEntity
{
    public function __construct()
    {
        parent::__construct();

        $this->properties = array_merge(
            $this->properties,
            [new ObjectsArrayProperty('allowed_entities', FileAllowedEntity::class)]
        );
    }
}
