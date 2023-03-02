<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use App\Services\MasterOutbox\Dto\CardUpdateDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Sxope\ValueObjects\Id;

class CardUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'card_name' => [
                'string',
                'max:100',
                'nullable',
                Rule::unique('mo_cards', 'card_name')
                    ->whereNotIn('card_id', [Id::create($this->route()?->parameter('cardId'))])
                    ->whereNull('deleted_at')
            ],
            'logo' => [
                'file',
                'mimes:jpeg,jpg,bmp,png',
                'max:500',
                'nullable',
            ],
            'show_on_dashboard' => 'required|string',
            'delete_logo' => 'string|exclude_with:logo',
        ];
    }

    public function toDto(): CardUpdateDto
    {
        return new CardUpdateDto(
            $this->get('card_name'),
            $this->file('logo'),
            filter_var($this->get('show_on_dashboard'), FILTER_VALIDATE_BOOLEAN),
            filter_var($this->get('delete_logo'), FILTER_VALIDATE_BOOLEAN)
        );
    }
}
