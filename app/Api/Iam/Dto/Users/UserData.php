<?php

declare(strict_types=1);

namespace App\Api\Iam\Dto\Users;

class UserData
{
    public function __construct(
        public readonly string $userId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly ?string $phoneNumber = null,
        /**
         * @var UserGroup[]|null
         */
        public readonly ?array $groups = null,
        /**
         * @var UserRole[]|null
         */
        public readonly ?array $roles = null,
    ) {
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }
}
