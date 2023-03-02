<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use Illuminate\Foundation\Http\FormRequest;

class MarkReadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_read' => 'required|boolean'
        ];
    }

    public function isMarkAsRead(): bool
    {
        return $this->get('is_read');
    }
}
