<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class MasterOutboxFileCreateRequest
 *
 * @package App\Http\Requests
 */
class MasterOutboxFileCreateRequest extends FormRequest
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
            'entity_id' => 'required|uuid_or_hex',
            'master_outbox_file_type_id' => 'required|exists_integer:master_outbox_file_types,master_outbox_file_type_id',
            'master_outbox_file_entity_type_id' => 'required|exists_integer:master_outbox_file_entity_types,master_outbox_file_entity_type_id',
            'master_outbox_file_visibility_type_id' => 'required|exists_integer:master_outbox_file_visibility_types,master_outbox_file_visibility_type_id',
            'description' => 'nullable|string|max:5000',
            'assigned_period' => 'required|date|date_format:Y-m-d',
            'attachment' => 'required|mimes:pdf|max:50000'
        ];
    }
}
