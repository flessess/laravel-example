<?php

namespace App\Services\BaseSpanner\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Support for binary uuid keys
 */
class SpannerHasMany extends HasMany
{
    use SpannerHasOneOrManyTrait;
}
