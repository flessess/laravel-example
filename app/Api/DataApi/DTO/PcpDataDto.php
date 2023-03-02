<?php

declare(strict_types=1);

namespace App\Api\DataApi\Dto;

class PcpDataDto
{
    public function __construct(
        public readonly string $pcpId,
        public readonly ?string $pcpGroup,
        public readonly array $personaPcpGroups,
        public readonly bool $isActive,
        public readonly string $pcpName,
        public readonly ?array $activePayerIds = null,
        public readonly ?string $personaEntityId = null,
        public readonly ?array $personaEntities = null,
        public readonly ?array $personaPcpNpiPcpGroups = null,
        public readonly ?array $personaEntityExtendedAttributesPcp = null,
        public readonly ?string $pcpSubgroup = null,
        public readonly ?int $npi = null,
    ) {
    }
}
