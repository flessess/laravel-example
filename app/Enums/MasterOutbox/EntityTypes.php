<?php

declare(strict_types=1);

namespace App\Enums\MasterOutbox;

enum EntityTypes: int
{
    case PCP = 1;
    case PCP_GROUP = 2;
    case PAYER = 3;
    case USER = 4;
    case USER_GROUP = 5;
    case USER_ROLE = 6;

    public function getName(): string
    {
        return str_replace('_', ' ', $this->name);
    }

    public static function getNames(): array
    {
        return array_map(static fn (EntityTypes $type) => $type->getName(), self::cases());
    }

    public static function isIdExists(int $id): bool
    {
        return self::tryFrom($id) !== null;
    }
}
