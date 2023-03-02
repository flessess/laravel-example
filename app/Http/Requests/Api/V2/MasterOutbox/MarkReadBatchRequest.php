<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string[] $file_ids
 * @property bool $is_read
 */
class MarkReadBatchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_read' => 'required|boolean',
            'file_ids' => 'required|array',
            'file_ids.*' => 'required|uuid_or_hex',
        ];
    }
}
