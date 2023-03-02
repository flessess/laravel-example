<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox\Dto;

use Illuminate\Http\UploadedFile;

class CardCreateDto
{
    public function __construct(
        public string $cardName,
        public ?UploadedFile $logo,
        public bool $showOnDashboard
    ) {
    }
}
