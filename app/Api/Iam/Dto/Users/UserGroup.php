<?php

declare(strict_types=1);

namespace App\Api\Iam\Dto\Users;

class UserGroup
{
    public function __construct(public readonly string $id, public readonly string $name)
    {
    }
}
