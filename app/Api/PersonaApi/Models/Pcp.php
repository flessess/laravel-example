<?php

declare(strict_types=1);

namespace App\Api\PersonaApi\Models;

class Pcp
{
    public function __construct(
        public readonly string $entityId,
        public readonly array $pcpGroups,
        public readonly bool $isNew,
        public readonly string $pcpNpi,
        public readonly ?bool $needReview = null,
        public readonly ?bool $isContractExpired = null,
    ) {
    }
}
