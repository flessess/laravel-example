<?php

declare(strict_types=1);

namespace App\Http\Attributes\Schemas\Requests\V1;

use OpenApi\Attributes\Items;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\ElementCollection;
use Sxope\Http\Attributes\Parameters\Collections\Pagination;
use Sxope\Http\Attributes\Properties\DateProperty;
use Sxope\Http\Attributes\Properties\StringProperty;

class MasterOutboxGetFilesListRequestParameters extends ElementCollection
{
    public function getListScope(): array
    {
        return array_merge(
            (new Pagination('page'))->toArray(),
            [
                new Parameter(
                    name: 'entity_id[]', description: 'Filter data by entity_id ids list', in: 'query', required: false,
                    schema: new Schema(type: 'array', items: new Items(type: 'string', example: 'b4f67f6-a5dd-4bc4-9182-9e4d90976840'))
                ),
                new Parameter(
                    name: 'pcp_id[]', description: 'Filter data by pcp_id ids list', in: 'query', required: false,
                    schema: new Schema(type: 'array', items: new Items(type: 'string', example: 'b4f67f6-a5dd-4bc4-9182-9e4d90976840'))
                ),
                new Parameter(
                    name: 'master_outbox_file_visibility_type_id[]', description: 'Filter data by master_outbox_file_visibility_type_id list', in: 'query', required: false,
                    schema: new Schema(type: 'array', items: new Items(type: 'integer', example: 1))
                ),
                new Parameter(
                    name: 'master_outbox_file_type_id[]', description: 'Filter data by master_outbox_file_type_id list', in: 'query', required: false,
                    schema: new Schema(type: 'array', items: new Items(type: 'integer', example: 1))
                ),
                new Parameter(
                    name: 'master_outbox_file_entity_type_id[]', description: 'Filter data by master_outbox_file_entity_type_id list', in: 'query', required: false,
                    schema: new Schema(type: 'array', items: new Items(type: 'integer', example: 1))
                ),
                new Parameter(
                    name: 'created_at', description: 'Filter files by dates range', in: 'query', required: false,
                    schema: new Schema(
                        properties: [
                            new DateProperty('from'),
                            new DateProperty('to'),
                        ],
                        type: 'object'
                    )
                ),
                new Parameter(
                    name: 'sort', description: 'Order data by list of fields', in: 'query', required: false,
                    schema: new Schema(
                        type: 'array',
                        items: new Items(
                            properties: [
                                new StringProperty('field', example: 'created_at', enum: ['created_at', 'updated_at', 'assigned_period']),
                                new StringProperty('direction', example: 'asc', enum: ['asc', 'desc']),
                            ],
                            type: 'object'
                        )
                    )
                ),
            ]
        );
    }

    public function getAggregatedListScope(): array
    {
        return [
            new Parameter(
                name: 'pcp_id[]', description: 'Filter data by pcp_id ids list', in: 'query', required: false,
                schema: new Schema(type: 'array', items: new Items(type: 'string', example: 'b4f67f6-a5dd-4bc4-9182-9e4d90976840'))
            ),
            new Parameter(
                name: 'created_at', description: 'Filter files by dates range', in: 'query', required: false,
                schema: new Schema(
                    properties: [
                        new DateProperty('from'),
                        new DateProperty('to'),
                    ],
                    type: 'object'
                )
            ),
        ];
    }
}
