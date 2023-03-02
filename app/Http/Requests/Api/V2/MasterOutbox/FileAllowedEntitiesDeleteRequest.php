<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property array $file_allowed_entity_id
 */
class FileAllowedEntitiesDeleteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file_allowed_entity_id' => 'required|array',
            'file_allowed_entity_id.*' => 'required|uuid_or_hex',
        ];
    }
}
