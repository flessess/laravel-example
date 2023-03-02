<?php

namespace App\Api\PersonaApi;

use App\Api\PersonaApi\Responses\FindPcpByContactResponse;
use App\Api\PersonaApi\Responses\GetPcpGroupListResponse;
use App\Api\PersonaApi\Responses\GetPcpListResponse;
use App\Api\PersonaApi\Contracts\PersonaApiClientInterface;
use App\Api\PersonaApi\Models\PcpGroup;
use Sxope\ValueObjects\Id;

class PersonaDummyApiClient implements PersonaApiClientInterface
{
    /**
     * @var PcpGroup[]
     */
    public array $pcpGroup = [];

    public function getPcpGroups(string $search = null): GetPcpGroupListResponse
    {
        $groups = [];
        foreach ($this->pcpGroup as $item) {
            if ($item->pcpGroupName === $search) {
                $groups[] = $item;
            }
        }

        return new GetPcpGroupListResponse($groups);
    }

    public function addGroup(string $groupName, Id $groupId): void
    {
        $this->pcpGroup[$groupName] = new PcpGroup(
            $groupId->getHex(),
            $groupName,
            false,
            false,
            false
        );
    }

    public function getPcpGroupByName(string $name): ?PcpGroup
    {
        return $this->getPcpGroups($name)->data[0] ?? null;
    }

    public function findByContact($term): FindPcpByContactResponse
    {
        return new FindPcpByContactResponse();
    }

    public function getPcpList(): GetPcpListResponse
    {
        return new GetPcpListResponse();
    }
}
