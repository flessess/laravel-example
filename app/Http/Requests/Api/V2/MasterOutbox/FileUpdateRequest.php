<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use App\Services\MasterOutbox\Dto\FileUpdateDto;
use Illuminate\Foundation\Http\FormRequest;
use Sxope\ValueObjects\Id;

class FileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'card_id' => 'uuid_or_hex|exists_bytes:mo_cards,card_id',
            'use_card_permissions' => 'numeric|prohibits:allowed_entities',
        ];
    }

    public function toDto(): FileUpdateDto
    {
        $useCardPermissions = $this->has('use_card_permissions') ? $this->get('use_card_permissions', false) : null;

        return new FileUpdateDto(
            $this->has('card_id') ? Id::create($this->get('card_id')) : null,
            (boolean) $useCardPermissions,
        );
    }
}
