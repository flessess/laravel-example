<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class PayerFileUpdateRequest
 *
 * @package App\Http\Requests
 */
class PayerFileUpdateRequest extends FormRequest
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
            'payer_file_type_id' => 'required|exists_integer:payer_file_types,payer_file_type_id',
            'payer_file_visibility_type_id' => 'required|exists_integer:payer_file_visibility_types,payer_file_visibility_type_id',
            'description' => 'nullable|string|max:5000',
            'assigned_period' => 'required|date|date_format:Y-m-d',
        ];
    }
}
