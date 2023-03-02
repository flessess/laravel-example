<?php

declare(strict_types=1);

namespace App\Api\Iam\Dto\Roles;

class Role
{
    public function __construct(
        public readonly ?string $description,
        public readonly string $name,
        public readonly string $roleId,
    ) {
    }
}
