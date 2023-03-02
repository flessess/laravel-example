<?php

declare(strict_types=1);

namespace App\Api\DataApi;

use App\Api\DataApi\Contracts\DataApiClientInterface;
use App\Api\DataApi\Contracts\GatewayDataApiClientInterface;
use App\Api\DataApi\Dto\PcpDataDto;
use App\Api\DataApi\Responses\GetPcpsDataResponse;
use Sxope\Api\ApiClient;
use Sxope\Services\Serializer\Elements\ArrayItem;
use Sxope\ValueObjects\Id;

class DataApiClient extends ApiClient implements DataApiClientInterface, GatewayDataApiClientInterface
{
    public function getPcpData(string $pcpId): ?PcpDataDto
    {
        return count($this->getPcpsData([$pcpId])->data) === 1 ? $this->getPcpsData([$pcpId])->data[0] : null;
    }

    public function getPcpsData(array $pcps = null): GetPcpsDataResponse
    {
        $data = [
            'available_fields' => [
                'pcp_id',
                'pcp_group',
                'is_active',
                'persona_pcp_groups',
                'active_payer_ids',
                'persona_entity_id',
                'pcp_name',
                'persona_entities',
                'persona_entity_extended_attributes__pcp',
                'pcp_subgroup',
                'persona_pcp_npi_pcp_groups',
                'npi'
            ],
            'filter' => [
                'is_active' => [
                    'contains' => [
                        'true'
                    ]
                ]
            ]
        ];

        if (!is_null($pcps)) {
            $data['filter']['pcp_id']['contains'] = array_map(
                static fn(Id $id) => $id->getHex(), Id::batchCreate($pcps)
            );
        }

        return $this->walkPages(function ($page, $limit) use ($data) {
            $response = $this->post(
                "browse/clean/accelerated/pcps?limit=$limit&offset=" . $page * $limit,
                ['json' => $data]
            );

            return $this->handleResponse(
                $response,
                GetPcpsDataResponse::class,
                [ArrayItem::create(PcpDataDto::class)],
            );
        }, 0);
    }
}
