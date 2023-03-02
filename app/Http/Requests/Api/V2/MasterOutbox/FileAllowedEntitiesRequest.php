<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use App\Enums\MasterOutbox\EntityTypes;
use App\Services\MasterOutbox\Dto\FileAllowedEntityDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Sxope\ValueObjects\Id;

/**
 * @property int $entity_type_id
 * @property string $entity_id
 */
class FileAllowedEntitiesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'allowed_entities' => 'required|array',
            'allowed_entities.*.entity_type_id' => [
                'required',
                'integer',
                static fn ($name, $value, $fail)
                => EntityTypes::isIdExists($value) ? true : $fail($name . ' doesn\'t exists'),
            ],
            'allowed_entities.*.entity_id' => 'required|uuid_or_hex',
        ];
    }

    public function toDtos(): Collection
    {
        return collect(
            array_map(
                static fn (array $data)
                => new FileAllowedEntityDto($data['entity_type_id'], Id::create($data['entity_id'])),
                $this->get('allowed_entities')
            )
        );
    }
}
