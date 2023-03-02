<?php

namespace App\Services\BaseSpanner\Eloquent\Relations;

use App\Services\BaseSpanner\BaseSpannerModelTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Support for binary uuid keys
 */
class SpannerPivot extends Pivot
{
    use BaseSpannerModelTrait;
}
