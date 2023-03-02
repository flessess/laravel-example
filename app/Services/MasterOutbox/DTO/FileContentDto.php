<?php

declare(strict_types=1);

namespace App\Services\MasterOutbox\Dto;

class FileContentDto
{
    public function __construct(
        public readonly string $fileContent,
        public readonly string $extension,
        public readonly string $mimeType,
    ) {
    }
}
