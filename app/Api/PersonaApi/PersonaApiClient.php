<?php

declare(strict_types=1);

namespace App\Api\PersonaApi;

use App\Api\PersonaApi\Contracts\GatewayPersonaApiClientInterface;
use App\Api\PersonaApi\Contracts\PersonaApiClientInterface;
use App\Api\PersonaApi\Models\Pcp;
use App\Api\PersonaApi\Models\PcpGroup;
use App\Api\PersonaApi\Responses\FindPcpByContactResponse;
use App\Api\PersonaApi\Responses\GetPcpGroupListResponse;
use App\Api\PersonaApi\Responses\GetPcpListResponse;
use Sxope\Api\ApiClient;
use Sxope\Services\Serializer\Elements\ArrayItem;
use Sxope\ValueObjects\Id;

class PersonaApiClient extends ApiClient implements PersonaApiClientInterface, GatewayPersonaApiClientInterface
{
    public function getPcpGroupByName(string $name, Id $userId = null): ?PcpGroup
    {
        $data = $this->getPcpGroups($name, $userId);

        if (count($data->data) === 1) {
            return $data->data[0];
        }

        if (count($data->data) > 1) {
            foreach ($data->data as $datum) {
                if ($datum->pcpGroupName === $name) {
                    return $datum;
                }
            }
        }

        return null;
    }

    public function getPcpGroups(string $search = null, Id $userId = null): GetPcpGroupListResponse
    {
        $headers = [];

        if ($userId !== null) {
            $headers['X-SSO-USER-ID'] = $userId->getUuid();
        }

        return $this->walkPages(function (int $page, int $limit) use ($headers, $search) {
            $offset = $page * $limit;

            return $this->handleResponse(
                $this->post(
                    'api/v1/pcp-group/list?offset=' . $offset . '&limit=' . $limit,
                    [
                        'json' => [
                            'available_fields' => [
                                'pcp_group_name',
                                'group_npi',
                            ],
                            'search' => $search
                        ],
                        'headers' => $headers,
                    ]
                ),
                GetPcpGroupListResponse::class,
                [ArrayItem::create(PcpGroup::class)]
            );
        }, 0);
    }

    public function getPcpList(): GetPcpListResponse
    {
        return $this->walkPages(function ($page, $limit) {
            $offset = $page * $limit;
            return $this->handleResponse(
                $this->post(
                    'api/v1/pcp/list',
                    [
                        'json' => [
                            'available_fields' => [
                                'pcp_groups',
                                'pcp_npi'
                            ],
                            'status' => [
                                'contains' => [
                                    'Active'
                                ]
                            ],
                            'limit' => $limit,
                            'offset' => $offset,
                        ]
                    ]
                ),
                GetPcpListResponse::class,
                [ArrayItem::create(Pcp::class)]
            );
        }, 0, 3000);
    }

    public function findByContact($term): FindPcpByContactResponse
    {
        return $this->handleResponse(
            $this->get(
                'api/v1/external/pcp/find-by-contact',
                ['query' => ['contact' => $term]]
            ),
            FindPcpByContactResponse::class
        );
    }
}
