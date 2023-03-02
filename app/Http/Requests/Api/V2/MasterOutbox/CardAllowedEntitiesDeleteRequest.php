<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property array $card_allowed_entity_id
 */
class CardAllowedEntitiesDeleteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'card_allowed_entity_id' => 'required|array',
            'card_allowed_entity_id.*' => 'required|uuid_or_hex',
        ];
    }
}
