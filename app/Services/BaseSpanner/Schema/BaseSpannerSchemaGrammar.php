<?php

namespace App\Services\BaseSpanner\Schema;

use Colopl\Spanner\Schema\Grammar as ColoplGrammar;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;

/**
 * Schema grammar
 */
class BaseSpannerSchemaGrammar extends ColoplGrammar
{
    /**
     * The possible column modifiers.
     *
     * @var array
     */
    protected $modifiers = ['Nullable', 'Generated'];

    /**
     * Create the column definition for a decimal type.
     *
     * @inheritDoc
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeNumeric(Fluent $column)
    {
        return "numeric";
    }

    /**
     * Compile an add column command.
     *
     * @inheritDoc
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent<string, mixed> $command
     * @return array
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        return $this->createAlterForEachColumn($blueprint, $this->prefixArray('ADD COLUMN', $this->getColumns($blueprint)));
    }

    /**
     * Compile a change column command into a series of SQL statements.
     *
     * @inheritDoc
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent<string, mixed> $command
     * @param  Connection $connection
     * @return array
     *
     * @throws \RuntimeException
     */
    public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        return $this->createAlterForEachColumn($blueprint, $this->prefixArray('ALTER COLUMN', $this->getChangedColumns($blueprint)));
    }

    /**
     * Compile a drop column command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent<string, mixed> $command
     * @return array
     */
    public function compileDropColumn(Blueprint $blueprint, Fluent $command)
    {
        return $this->createAlterForEachColumn($blueprint, $this->prefixArray('DROP COLUMN', $this->wrapArray($command->columns)));
    }

    /**
     * Compile alert table for each column.
     *
     * @param  Blueprint  $blueprint
     * @param  array  $columns
     * @return array
     */
    private function createAlterForEachColumn($blueprint, array $columns)
    {
        $result = [];
        foreach ($columns as $column) {
            $result[] = 'ALTER TABLE ' . $this->wrapTable($blueprint) . ' ' . $column;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function compileTableListing()
    {
        return "SELECT table_name FROM information_schema.tables WHERE table_catalog = '' AND table_schema = ''";
    }

    /**
     * Create the column definition for a generatable column.
     *
     * @inheritDoc
     *
     * @param  Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $column
     * @return ?string
     */
    protected function modifyGenerated(Blueprint $blueprint, Fluent $column)
    {
        if (is_null($column->generatedAs)) {
            return null;
        }

        return " AS ($column->generatedAs) STORED";
    }

    /**
     * @return string
     */
    public function compileFullColumnListing()
    {
        return 'SELECT * FROM information_schema.columns WHERE table_schema = \'\' AND table_name = ? AND column_name = ?';
    }
}
