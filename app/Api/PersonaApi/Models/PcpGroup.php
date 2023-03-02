<?php

declare(strict_types=1);

namespace App\Api\PersonaApi\Models;

class PcpGroup
{
    public function __construct(
        public readonly string $entityId,
        public readonly string $pcpGroupName,
        public readonly bool $isSubgroup,
        public readonly ?string $groupNpi,
        public readonly ?bool $needReview,
    ) {
    }
}
