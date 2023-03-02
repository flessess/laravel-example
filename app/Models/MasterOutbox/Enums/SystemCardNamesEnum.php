<?php

declare(strict_types=1);

namespace App\Models\MasterOutbox\Enums;

enum SystemCardNamesEnum: string
{
    case PCP = 'PCP';
    case PCP_GROUP = 'PCP_GROUP';
    case PAYER = 'PAYER';
    case USER = 'USER';
}
