<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class MasterOutboxFileUpdateViewStatusRequest
 *
 * @package App\Http\Requests
 */
class MasterOutboxFileUpdateViewStatusRequest extends FormRequest
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
            'view_status' => 'required|string|in:true,false',
        ];
    }
}
