<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V2\MasterOutbox;

use App\Services\MasterOutbox\Dto\FileAllowedEntityDto;
use App\Services\MasterOutbox\Dto\FileCreateDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Sxope\ValueObjects\Id;

class FileCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:jpeg,jpg,bmp,png,pdf,doc,img,docx,tif,tiff,rtf,csv,tsv,zip,txt,text',
                'max:100000',
            ],
            'description' => 'required|string|max:255',
            'assigned_period' => 'date_format:Y-m-d',
            'card_id' => 'required|exists_bytes:mo_cards,card_id',
            'use_card_permissions' => 'numeric',
        ];
    }

    public function toDto(): FileCreateDto
    {
        return new FileCreateDto(
            $this->file('file'),
            $this->get('description'),
            $this->get('assigned_period'),
            Id::create($this->get('card_id')),
            $this->get('has') ? (boolean) $this->get('use_card_permissions') : null,
        );
    }
}
