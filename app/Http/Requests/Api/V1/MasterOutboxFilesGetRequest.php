<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class MasterOutboxFilesGetRequest
 *
 * @package App\Http\Requests
 */
class MasterOutboxFilesGetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'pcp_id' => 'array',
            'pcp_id.*' => 'uuid_or_hex',
            'entity_id' => 'array',
            'entity_id.*' => 'uuid_or_hex',
            'master_outbox_file_visibility_type_id.*' => 'integer',
            'master_outbox_file_type_id.*' => 'integer',
            'master_outbox_file_entity_type_id.*' => 'integer',
            'sort' => 'array',
            'sort.*.field' => [
                'required',
                'string',
                Rule::in(['created_at', 'updated_at', 'assigned_period']),
            ],
            'sort.*.direction' => [
                'required',
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ];
    }
}
