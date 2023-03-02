<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox\Dto;

use Illuminate\Contracts\Support\Arrayable;
use Sxope\ValueObjects\Id;

class FileAllowedEntityDto implements Arrayable
{
    public function __construct(public int $entityTypeId, public Id $entityId)
    {
    }

    public function toArray(): array
    {
        return [
            'entity_type_id' => $this->entityTypeId,
            'entity_id' => $this->entityId->getHex(),
        ];
    }
}
