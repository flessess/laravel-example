<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox\Dto;

use Illuminate\Support\Collection;
use Sxope\ValueObjects\Id;

class FileUpdateDto
{
    public function __construct(
        public ?Id $cardId,
        public ?bool $useCardPermissions,
    ) {
    }
}
