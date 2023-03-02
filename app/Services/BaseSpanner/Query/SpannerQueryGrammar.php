<?php

namespace App\Services\BaseSpanner\Query;

use App\Services\BaseSpanner\Query\SpannerQueryBuilder;
use Colopl\Spanner\Query\Builder as ColoplQueryBuilder;
use Colopl\Spanner\Query\Grammar;
use Illuminate\Database\Query\Builder as LaravelQueryBuilder;

/**
 * Support for join hints
 */
class SpannerQueryGrammar extends Grammar
{
    /**
     * @inheritDoc
     */
    protected function compileFrom(LaravelQueryBuilder $query, $table)
    {
        assert($query instanceof SpannerQueryBuilder);

        return 'from ' . $this->wrapTable($table) . $this->compileTableHintExpr($query) . $this->compileAlias($query);
    }

    /**
     * Compile the "join" portions of the query.
     *
     * @param  LaravelQueryBuilder  $query
     * @param  array  $joins
     * @return string
     */
    protected function compileJoins(LaravelQueryBuilder $query, $joins)
    {
        return collect($joins)->map(function ($join) use ($query) {

            $table = $this->wrapTable(
                $join->table
            ) . $this->compileTableHintExpr($join) . $this->compileAlias($join);

            $nestedJoins = is_null($join->joins) ? '' : ' ' . $this->compileJoins($query, $join->joins);

            $tableAndNestedJoins = is_null($join->joins) ? $table : '(' . $table . $nestedJoins . ')';

            assert($join instanceof SpannerJoinClause, '$join must be App\Services/BaseSpanner\Query\SpannerJoinClause');

            return trim("{$join->type} join {$this->compileJoinHintExpr($join)} {$tableAndNestedJoins} {$this->compileWheres($join)}");
        })->implode(' ');
    }

    /**
     * @param  LaravelQueryBuilder  $query
     * @return string
     */
    protected function compileAlias($query)
    {
        $alias = $query->asAlias ?? null;
        return $alias ? " as `$alias`" : '';
    }

    /**
     * @param  LaravelQueryBuilder  $query
     * @return string
     */
    protected function compileTableHintExpr($query)
    {
        $tableHints = [];

        $expression = $this->compileForceIndexTableHintKey($query);
        if (!empty($expression)) {
            $tableHints[] = $expression;
        }

        $expression = $this->compileGroupByScanOptimizationTableHintKey($query);
        if (!empty($expression)) {
            $tableHints[] = $expression;
        }

        return  !empty($tableHints) ?  "@{" . implode(",", $tableHints) . "}" : '';
    }

    /**
     * @return string
     */
    protected function compileForceIndexTableHintKey($query)
    {
        $forceIndex = $query->forceIndex ?? null;

        return $forceIndex ? "FORCE_INDEX=$forceIndex" : '';
    }

    /**
     * @param  LaravelQueryBuilder  $query
     * @return string
     */
    protected function compileGroupByScanOptimizationTableHintKey($query)
    {
        $groupByScanOptimization = $query->groupByScanOptimization ?? null;
        if ($groupByScanOptimization === null) {
            return '';
        }

        return "GROUPBY_SCAN_OPTIMIZATION=" . ($groupByScanOptimization ? "TRUE" : "FALSE");
    }

    protected function compileJoinHintExpr(SpannerJoinClause $join)
    {
        $joinHintExpressions = [];

        $expression = $this->compileForceJoinOrderJoinHintKey($join);
        if (!empty($expression)) {
            $joinHintExpressions[] = $expression;
        }

        $expression = $this->compileJoinMethodJoinHintKey($join);
        if (!empty($expression)) {
            $joinHintExpressions[] = $expression;
        }

        $expression = $this->compileHashJoinBuildSideJoinHintKey($join);
        if (!empty($expression)) {
            $joinHintExpressions[] = $expression;
        }

        $expression = $this->compileBatchModeJoinHintKey($join);
        if (!empty($expression)) {
            $joinHintExpressions[] = $expression;
        }

        return !empty($joinHintExpressions) ? "@{" . implode(",", $joinHintExpressions) . "}" : '';
    }

    protected function compileJoinMethodJoinHintKey(SpannerJoinClause $join)
    {
        // HASH_JOIN
        // APPLY_JOIN

        $joinMethod = $join->joinMethod ?? null;

        return $joinMethod ? "JOIN_METHOD=$joinMethod" : '';
    }

    protected function compileForceJoinOrderJoinHintKey(SpannerJoinClause $join)
    {
        if ($join->forceJoinOrder === null) {
            return '';
        }

        return "FORCE_JOIN_ORDER=" . ($join->forceJoinOrder ? "TRUE" : "FALSE");
    }

    protected function compileHashJoinBuildSideJoinHintKey(SpannerJoinClause $join)
    {
        // BUILD_LEFT
        // BUILD_RIGHT

        $hashJoinBuildSide = $join->hashJoinBuildSide ?? null;

        return $hashJoinBuildSide ? "HASH_JOIN_BUILD_SIDE=$hashJoinBuildSide" : '';
    }

    protected function compileBatchModeJoinHintKey(SpannerJoinClause $join)
    {
        if ($join->batchMode === null) {
            return '';
        }

        return "BATCH_MODE=" . ($join->batchMode ? "TRUE" : "FALSE");
    }

    /**
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value === '*') {
            return $value;
        }

        return '`' . str_replace('`', '``', $value) . '`';
    }

    /**
     * @return bool
     */
    public function supportsSavepoints()
    {
        return false;
    }
}
