<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class PayerFilesGetRequest
 *
 * @package App\Http\Requests
 */
class PayerFilesGetRequest extends FormRequest
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
            'payer_id' => 'array',
            'payer_id.*' => 'uuid_or_hex',
            'payer_file_visibility_type_id.*' => 'integer',
            'payer_file_type_id.*' => 'integer',
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
