<?php

declare(strict_types=1);

namespace App\Api\Iam\Facades;

use App\Api\Iam\Contracts\GatewayIamApiClientInterface;
use App\Api\Iam\Dto\Users\UserData;
use Illuminate\Support\Facades\Facade;
use Sxope\ValueObjects\Id;

/**
 * @method static UserData getUserData(Id $userId)
 */
class GatewayIamApiClientFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return GatewayIamApiClientInterface::class;
    }
}
