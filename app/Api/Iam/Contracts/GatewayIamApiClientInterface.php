<?php

declare(strict_types=1);

namespace App\Api\Iam\Contracts;

use App\Api\Iam\Dto\Users\UserData;
use Sxope\ValueObjects\Id;

interface GatewayIamApiClientInterface
{
    public function getUserData(Id $userId): ?UserData;
}
