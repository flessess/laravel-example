<?php

namespace App\Services\BaseSpanner\Schema;

use Closure;
use Colopl\Spanner\Schema\Builder as ColoplSchemaBuilder;

/**
 * @property BaseSpannerSchemaGrammar $grammar
 */
class BaseSpannerSchemaBuilder extends ColoplSchemaBuilder
{
    /**
     * @inheritDoc
     */
    protected function createBlueprint($table, Closure $callback = null)
    {
        return isset($this->resolver)
            ? call_user_func($this->resolver, $table, $callback)
            : new BaseSpannerSchemaBlueprint($table, $callback);
    }

    /**
     * @return string[]
     */
    public function getTableListing()
    {
        $results = $this->connection->select(
            $this->grammar->compileTableListing()
        );

        return array_column($results, 'table_name');
    }

    /**
     * @param string $table
     * @param string $name
     * @param array $columns
     */
    public function createIndex($table, $name, array $columns)
    {
        $blueprint = $this->createBlueprint($table);
        $blueprint->index($columns, $name);
        $this->build($blueprint);
    }

    /**
     * @param string $table
     * @param string $column
     */
    public function isGeneratedColumn($table, $column)
    {
        $columnData = $this->connection->select(
            $this->grammar->compileFullColumnListing(),
            [$table, $column]
        );

        return $columnData[0]['IS_GENERATED'] == 'ALWAYS';
    }
}
