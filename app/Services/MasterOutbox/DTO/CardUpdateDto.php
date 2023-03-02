<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox\Dto;

use Illuminate\Http\UploadedFile;
use Sxope\ValueObjects\Id;

class CardUpdateDto
{
    public function __construct(
        public ?string $cardName,
        public ?UploadedFile $logo,
        public bool $showOnDashboard,
        public bool $deleteLogo,
    ) {
    }
}
