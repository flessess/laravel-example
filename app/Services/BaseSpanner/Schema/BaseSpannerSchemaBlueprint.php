<?php

namespace App\Services\BaseSpanner\Schema;

use Colopl\Spanner\Schema\Blueprint as ColoplBlueprint;

/**
 * Helper methods
 */
class BaseSpannerSchemaBlueprint extends ColoplBlueprint
{
    /**
     * BYTE(16) type
     *
     * @param string $column
     */
    public function binaryUuid($column)
    {
        return $this->binary($column, 16);
    }

    /**
     * BYTE(32) type
     *
     * @param string $column
     */
    public function binaryHash($column)
    {
        return $this->binary($column, 32);
    }

    /**
     * Create a new decimal column on the table.
     *
     * @param  string  $column
     * @param  int  $total
     * @param  int  $places
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function numeric($column, $total = 8, $places = 2)
    {
        return $this->addColumn('numeric', $column, [
            'total' => $total,
            'places' => $places,
            'unsigned' => false,
        ]);
    }
}
