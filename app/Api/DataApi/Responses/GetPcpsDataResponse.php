<?php

declare(strict_types=1);

namespace App\Api\DataApi\Responses;

use Sxope\Api\ApiResponse;
use App\Api\DataApi\Dto\PcpDataDto;

class GetPcpsDataResponse extends ApiResponse
{
    /**
     * @var PcpDataDto[]
     */
    public mixed $data;

    public function getPcpIds(): array
    {
        return array_map(static fn(PcpDataDto $data) => $data->pcpId, $this->data);
    }

    public function getPcpGroups(): array
    {
        return collect($this->data)->filter(static fn(PcpDataDto $data) => !is_null($data->pcpGroup))
            ->pluck('pcpGroup')
            ->unique()
            ->toArray();
    }

    public function getActivePayers(): array
    {
        $activePayers = collect([]);
        foreach ($this->data as $item) {
            if ($item->activePayerIds !== null && $item->activePayerIds !== []) {
                $activePayers = $activePayers->merge(
                    array_map(static fn (array $payer) => $payer['payer_id'], $item->activePayerIds)
                );
            }
        }

        return $activePayers->unique()->toArray();
    }
}
