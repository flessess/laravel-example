<?php

declare(strict_types=1);

namespace App\Api\SphereApi\Dto;

class Payer
{
    public function __construct(public string $payerId, public string $payerName, public ?string $payerFullName = null)
    {
    }
}
