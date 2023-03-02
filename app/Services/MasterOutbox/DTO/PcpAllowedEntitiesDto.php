<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox\Dto;

class PcpAllowedEntitiesDto
{
    public function __construct(
        public readonly string $pcpId,
        public readonly array $groups,
        public readonly array $activePayerIds,
        public readonly string $pcpName,
    ) {
    }
}
