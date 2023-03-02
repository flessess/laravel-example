<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox\Dto;

use Sxope\ValueObjects\Id;

class CardAllowedEntityDto
{
    public function __construct(public int $entityTypeId, public Id $entityId)
    {
    }
}
