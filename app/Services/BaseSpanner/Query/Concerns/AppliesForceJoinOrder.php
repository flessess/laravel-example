<?php

namespace App\Services\BaseSpanner\Query\Concerns;

trait AppliesForceJoinOrder
{
    /**
     * @var ?bool
     */
    public $forceJoinOrder;

    /**
     * @param ?bool $forceJoinOrder
     * @return $this
     */
    public function forceJoinOrder(?bool $forceJoinOrder)
    {
        $this->forceJoinOrder = $forceJoinOrder;

        return $this;
    }
}
