<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;

class FilterRequest extends BaseApiV1Request
{
    public function rules()
    {
        return [
            'search' => 'string',

            'available_fields' => 'array',
            'available_fields.*' => 'string',

            'filters' => [
                'array',
            ],

            'filters.*' => [
                'array',
            ],
            'filters.*.from' => [
                $this->dateFormatWithTimeZone,

            ],
            'filters.*.to' => [
                $this->dateFormatWithTimeZone,
            ],
            'filters.*.contains' => [
                'array',
            ],

            'filters.*.notcontains' => [
                'array',
            ],

            'sort' => [
                'array',
            ],
            'sort.*' => [
                'array',
            ],
            'sort.*.field' => [
                'required',
                'string',
            ],
            'sort.*.direction' => [
                'required',
                Rule::in(['asc', 'desc']),
            ],
            'limit' => 'numeric',
            'offset' => 'numeric',
        ];
    }

    protected function prepareFiltersForValidation(array $fields = []): void
    {
        parent::prepareForValidation();

        if ($this->has('limit')) {
            $this->offsetSet('limit', (int)$this->offsetGet('limit'));
        }

        if ($this->has('offset')) {
            $this->offsetSet('offset', (int)$this->offsetGet('offset'));
        }

        if ($this->has('search') && !$this->filled('search')) {
            $this->offsetUnset('search');
        }
    }

    public function messages()
    {
        return array_merge(
            parent::messages(),
            [
                'sort.*.direction.in' =>
                    'Direction must be one of (' . implode(', ', ['asc', 'desc']) . ')',
            ]
        );
    }
}
