<?php

namespace App\Services\BaseSpanner\Eloquent;

use App\Services\BaseSpanner\BaseSpannerModelInteraface;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builder with spanner support and casting bytes based Uuids.
 */
class SpannerEloquentBuilder extends Builder
{
    /**
     * Add a basic where clause to the query.
     *
     * @param  \Closure|string|array|\Illuminate\Database\Query\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column instanceof Closure || is_array($column) || $value instanceof Closure) {
            $result = parent::where($column, $operator, $value, $boolean);
            assert($result === $this);
            return $result;
        }

        if (2 == func_num_args()) {
            $value = $operator;
        }

        assert($this->model instanceof BaseSpannerModelInteraface);
        $value = $this->model->applyCasts($column, $value);

        if (2 == func_num_args()) {
            $result = parent::where($column, $value);
            assert($result === $this);
            return $result;
        }

        $result = parent::where($column, $operator, $value, $boolean);
        assert($result === $this);
        return $result;
    }
}
