<?php

namespace App\Http\Requests\Api\V1;

use App\Models\FileSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UploadFileRequest
 *
 * @package App\Http\Requests
 */
class UploadFileRequest extends FormRequest
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
            'source' => [
                'required',
                Rule::in(FileSource::getSourceList()),
            ],
            'file' => [
                'required',
                'file',
                'mimes:jpeg,jpg,bmp,png,pdf,doc,docx,tif,tiff,rtf,csv,tsv,zip,txt,text',
                'max:500000',
            ],
            'file_type_id' => 'nullable|integer|exists_integer:file_types,file_type_id',
            'data_owner_id' => 'exists_bytes:synced_from_sphere_data_owners,data_owner_id',
            'skip_tagging' => 'boolean',
            'pcp_id' => 'uuid_or_hex',
            'member_id' => 'uuid_or_hex',
            'document_type_id' => 'uuid_or_hex',
            'date_of_service' => 'date_format:Y-m-d',
        ];
    }
}
