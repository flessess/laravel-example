<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox\Dto;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Sxope\ValueObjects\Id;

class FileCreateDto
{
    public function __construct(
        public UploadedFile $file,
        public ?string $description,
        public ?string $assignedPeriod,
        public Id $cardId,
        public ?bool $useCardPermissions
    ) {
    }
}
