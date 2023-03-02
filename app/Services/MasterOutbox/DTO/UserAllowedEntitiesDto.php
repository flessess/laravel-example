<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox\Dto;

use JsonSerializable;
use Sxope\ValueObjects\Id;

class UserAllowedEntitiesDto implements JsonSerializable
{
    public function __construct(
        public readonly string $userId,
        public readonly array $pcpIds,
        public readonly array $groupIds,
        public readonly array $activePayerIds,
        public readonly array $userGroups,
        public readonly array $userRoles,
        public readonly bool $fullAccess = false
    ) {
    }

    public function jsonSerialize(): array
    {
        $tempTableData = [];
        $tempTableData[] = $this->userId;

        foreach ($this->pcpIds as $pcpId) {
            $tempTableData[] = $pcpId;
        }

        foreach ($this->activePayerIds as $activePayerId) {
            $tempTableData[] = $activePayerId;
        }

        foreach ($this->groupIds as $groupId) {
            $tempTableData[] = $groupId;
        }

        foreach ($this->userRoles as $roleId) {
            $tempTableData[] = $roleId;
        }

        foreach ($this->userGroups as $groupId) {
            $tempTableData[] = $groupId;
        }

        return array_map(static fn (string $id) => Id::create($id)->getHex(), $tempTableData);
    }
}
