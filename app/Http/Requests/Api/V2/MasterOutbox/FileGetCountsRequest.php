<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

class FileGetCountsRequest extends FileGetListRequest
{
    public function additionalRules(): array
    {
        return [
            'available_fields' => 'array'
        ];
    }

    public static function getAvailableFields(): array
    {
        return [
            'card_id',
            'is_read',
        ];
    }

    public static function getSortingDefinitions(): array
    {
        return [];
    }
}
