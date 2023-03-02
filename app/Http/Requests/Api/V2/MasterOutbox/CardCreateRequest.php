<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use App\Services\MasterOutbox\Dto\CardCreateDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CardCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'card_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('mo_cards', 'card_name')
                    ->whereNull('deleted_at')
            ],
            'logo' => [
                'file',
                'mimes:jpeg,jpg,bmp,png',
                'max:500',
                'nullable',
            ],
            'show_on_dashboard' => 'required|string',
        ];
    }

    public function toDto(): CardCreateDto
    {
        return new CardCreateDto(
            $this->get('card_name'),
            $this->file('logo'),
            filter_var($this->get('show_on_dashboard'), FILTER_VALIDATE_BOOLEAN),
        );
    }
}
