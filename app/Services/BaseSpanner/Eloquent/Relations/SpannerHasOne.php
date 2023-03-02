<?php

namespace App\Services\BaseSpanner\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Support for binary uuid keys
 */
class SpannerHasOne extends HasOne
{
    use SpannerHasOneOrManyTrait;
}
