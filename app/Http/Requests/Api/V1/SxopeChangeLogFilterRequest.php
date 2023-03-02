<?php

namespace App\Http\Requests\Api\V1;

use App\Rules\AllowedArrayKeys;
use App\Services\SxopeLogService\SxopeChangeLogEntryBuilder;
use Illuminate\Validation\Rule;

class SxopeChangeLogFilterRequest extends FilterRequest
{
    private array $sortable = [
        'table_name',
        'operation_type',
        'changed_at',
    ];

    private $filterable = [
        'model_id',
        'model_type',
        'label',
        'attribute',
        'platform_user_id',
        'table_name',
        'changed_at',
        'operation_type',
        'description',
    ];

    protected array $uuidFields = ['filters.platform_user_id.contains.*'];

    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'filters.*' => [new AllowedArrayKeys('filters', $this->filterable)],
                'filters.*.contains.*' => 'string',
                'filters.platform_user_id.contains.*' => 'uuid',
                'filters.operation_type.contains.*' => [Rule::in(SxopeChangeLogEntryBuilder::OPERATION_TYPES)],
                'filters.changed_at.*' => 'date_format:Y-m-d',
                'filters.changed_at.to' => 'nullable|date_format:Y-m-d|after_or_equal:filters.changed_at.from',
                'filters.changed_at.from' => 'nullable|date_format:Y-m-d',
                'filters_search' => 'array',
                'filters_search.*' => [
                    'string',
                    new AllowedArrayKeys('filters_search', $this->filterable),
                ],
                'sort.*.field' => [Rule::in($this->sortable)],
                'limit' => 'nullable|integer|min:1|max:1000',
                'offset' => 'nullable|integer|min:0',
                'distinct' => 'boolean',
                'fields' => 'array',
                'fields.*' => [Rule::in($this->filterable)],
                'search' => 'string|min:3',
                'search_extended' => 'array',
                'search_extended.*' => [
                    'string',
                    'min:3',
                    new AllowedArrayKeys('search_extended', [
                        'model_type',
                        'label',
                        'attribute',
                        'table_name',
                        'description',
                    ]),
                ],
            ]
        );
    }

    public function messages(): array
    {
        return array_merge(
            parent::messages(),
            [
                'sort.*.field.in' => 'Sort fields must be on of (' . implode(', ', $this->sortable) . ')',
            ]
        );
    }
}
