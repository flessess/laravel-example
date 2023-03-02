<?php

namespace App\Services\BaseSpanner\Query;

use App\Services\BaseSpanner\Query\Concerns\AppliesAsAlias;
use App\Services\BaseSpanner\Query\Concerns\AppliesBatchMode;
use App\Services\BaseSpanner\Query\Concerns\AppliesForceJoinOrder;
use App\Services\BaseSpanner\Query\Concerns\AppliesGroupByScanOptimization;
use App\Services\BaseSpanner\Query\Concerns\AppliesHashJoinBuildSide;
use App\Services\BaseSpanner\Query\Concerns\AppliesJoinMethod;
use Colopl\Spanner\Query\Concerns\AppliesForceIndex;
use Illuminate\Database\Query\JoinClause;

/**
 * Support api-data helpers
 */
class SpannerJoinClause extends JoinClause
{
    use AppliesAsAlias;
    use AppliesBatchMode;
    use AppliesGroupByScanOptimization;
    use AppliesForceIndex;
    use AppliesForceJoinOrder;
    use AppliesHashJoinBuildSide;
    use AppliesJoinMethod;

    public const JOIN_METHOD_HASH_JOIN = 'HASH_JOIN';
    public const JOIN_METHOD_APPLY_JOIN = 'APPLY_JOIN';

    public const HASH_JOIN_BUILD_SIDE_LEFT = 'BUILD_LEFT';
    public const HASH_JOIN_BUILD_SIDE_RIGHT = 'BUILD_RIGHT';
}
