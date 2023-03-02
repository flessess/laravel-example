<?php

namespace App\Services\BaseSpanner\Query;

use App\Services\BaseSpanner\BaseSpannerModelInteraface;
use App\Services\BaseSpanner\Query\Concerns\AppliesAsAlias;
use App\Services\BaseSpanner\Query\Concerns\AppliesGroupByScanOptimization;
use Closure;
use Colopl\Spanner\Connection as ColoplConnection;
use Colopl\Spanner\Query\Builder as ColoplQueryBuilder;
use Colopl\Spanner\Query\Grammar as ColoplGrammar;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;

/**
 * Builder with spanner support and casting bytes based Uuids.
 */
class SpannerQueryBuilder extends ColoplQueryBuilder
{
    use AppliesAsAlias;
    use AppliesGroupByScanOptimization;

    private $model;
    /**
     * Create a new query builder instance.
     */
    public function __construct(
        ColoplConnection $connection,
        ?ColoplGrammar $grammar = null,
        ?Processor $processor = null,
        $model = null
    ) {
        $this->connection = $connection;
        $grammar ??= $connection->getQueryGrammar();
        assert($grammar instanceof ColoplGrammar);
        $this->grammar = $grammar;
        $this->processor = $processor ?: $connection->getPostProcessor();
        $this->model = $model;
    }

    /**
     * Add a basic where clause to the query.
     * add auto-casts for binary uuid
     *
     * @param  \Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column instanceof Closure || is_array($column) || $value instanceof Closure) {
            return parent::where($column, $operator, $value, $boolean);
        }

        if (2 == func_num_args()) {
            $value = $operator;
        }

        if ($this->model) {
            assert($this->model instanceof BaseSpannerModelInteraface);
            $value = $this->model->applyCasts($column, $value);
        }

        if (2 == func_num_args()) {
            return parent::where($column, $value);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * Add a "where in" clause to the query.
     * add auto-casts for binary uuid
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        if (
            $values instanceof Closure
            || $this->isQueryable($values)
        ) {
            return parent::whereIn($column, $values, $boolean, $not);
        }

        if ($this->model) {
            assert($this->model instanceof BaseSpannerModelInteraface);
            $values = $this->model->applyCastsValues($column, $values);
        }

        return parent::whereIn($column, $values, $boolean, $not);
    }

    /**
     * Retrieve column values from rows represented as arrays.
     *
     * @param  array  $queryResult
     * @param  string  $column
     * @param  ?string  $key
     * @return \Illuminate\Support\Collection
     */
    protected function pluckFromArrayColumn($queryResult, $column, $key)
    {
        $results = [];

        if (is_null($key)) {
            foreach ($queryResult as $row) {
                $results[] = $row[$column];
            }
        } else {
            foreach ($queryResult as $row) {
                $results[(string) $row[$key]] = $row[$column];
            }
        }

        return collect($results);
    }

    /**
     * Get a new instance of the query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function newQuery()
    {
        return new self($this->connection, $this->grammar, $this->processor, $this->model);
    }

    /**
     * Get a new join clause.
     *
     * @param  \Illuminate\Database\Query\Builder  $parentQuery
     * @param  string  $type
     * @param  string  $table
     * @return \Illuminate\Database\Query\JoinClause
     */
    protected function newJoinClause(Builder $parentQuery, $type, $table)
    {
        return new SpannerJoinClause($parentQuery, $type, $table);
    }
}
