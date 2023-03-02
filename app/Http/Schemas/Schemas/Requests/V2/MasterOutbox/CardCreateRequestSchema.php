<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Requests\V2\MasterOutbox;

use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\BooleanProperty;
use Sxope\Http\Attributes\Properties\FileProperty;
use Sxope\Http\Attributes\Properties\StringProperty;

class CardCreateRequestSchema extends Schema
{
    public function __construct()
    {
        parent::__construct(
            properties: [
                new StringProperty('card_name', 'Card name'),
                new FileProperty('logo', description: 'Only image files allowed with max size ~500KB'),
                new BooleanProperty('show_on_dashboard')
            ]
        );
    }
}
