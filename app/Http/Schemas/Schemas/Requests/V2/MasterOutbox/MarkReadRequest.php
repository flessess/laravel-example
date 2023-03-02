<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Requests\V2\MasterOutbox;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\BooleanProperty;

class MarkReadRequest extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new BooleanProperty('is_read')
            ]
        );
    }

}
