<?php

namespace App\Services\SxopeLogService;

use function isAssoc;

/**
 * Model or other structures comparer
 *
 * Class DataCompare
 * @package App\Helpers
 */
class SxopeChangeLogDataCompare
{
    const TYPE_MODELS        = 0;
    const TYPE_SIMPLE_ARRAYS = 1;
    const TYPE_ASSOC_ARRAYS  = 2;
    const TYPE_MODELS_ARRAYS = 3;
    const TYPE_SIMPLE_VALUE  = 4;
    const TYPES_NULLS        = 5;
    const TYPES_OTHER        = 6;

    public static function parseArray(
        array $array = null,
        callable $insertCallback = null,
        callable $updateCallback = null,
        callable $deleteCallback = null
    ) {
        if (!isset($array)) {
            return;
        }
        if (isset($array['insert'])) {
            foreach ($array['insert'] as $insert) {
                if (isset($insertCallback)) {
                    $insertCallback($insert);
                }
            }
        }
        if (isset($array['update'])) {
            foreach ($array['update'] as $key => $update) {
                if (isset($updateCallback)) {
                    $updateCallback($key, $update);
                }
            }
        }
        if (isset($array['delete'])) {
            foreach ($array['delete'] as $delete) {
                if (isset($deleteCallback)) {
                    $deleteCallback($delete);
                }
            }
        }
    }

    /**
     * Compare two entities
     *
     * @param array|mixed|null $value1 Old model
     * @param array|mixed|null $value2 New model
     * @return array Information for insert/update/delete operations
     */
    public static function compare($value1, $value2)
    {
        $typeValues = self::getTypes($value1, $value2);
        switch ($typeValues) {
            case self::TYPE_ASSOC_ARRAYS:
                return self::compareAssoc($value1, $value2);
            case self::TYPE_SIMPLE_ARRAYS:
                return self::compareSimpleArrays($value1, $value2);
            case self::TYPE_SIMPLE_VALUE:
            case self::TYPES_OTHER:
                return self::compareSimpleValues($value1, $value2);
            default:
                return [];
        }
    }

    /**
     * Compare flats arrays without order.
     *
     *
     * @param array|null $value1 Old array data
     * @param array|null $value2 New array data
     * @return array Information for insert/delete arrays or empty array if arrays is equals
     *          [
     *              'insert' => array|null - new array items
     *              'delete' => array|null - old array items
     *          ]
     */
    private static function compareSimpleArrays(array $value1 = null, array $value2 = null)
    {
        if (!isset($value1) && !isset($value2)) {
            return [];
        } elseif (isset($value1) && !isset($value2)) {
            return ['delete' => $value1];
        } elseif (!isset($value1) && isset($value2)) {
            return ['insert' => $value2];
        }
        $result = ['insert' => [], 'delete' => []];

        foreach ($value1 as $old) {
            if (array_search($old, $value2) === false) {
                $result['delete'][] = $old;
            }
        }
        foreach ($value2 as $new) {
            if (array_search($new, $value1) === false) {
                $result['insert'][] = $new;
            }
        }
        if (count($result['insert']) === 0) {
            unset($result['insert']);
        }
        if (count($result['delete']) === 0) {
            unset($result['delete']);
        }
        return $result;
    }

    /**
     * Compare associative arrays
     *
     * @param array|null $value1 Old array
     * @param array|null $value2 New array
     * @return array
     *          [
     *              For simple value property
     *              'prop' =>
     *                  [
     *                      'from' => old value,
     *                      'to' => new value
     *                  ]
     *              For array with simple values
     *              'prop' =>
     *                  [
     *                      'insert' => array|null,
     *                      'delete' => array|null
     *                  ]
     *              prop => For associative array
     *                  recursive
     *          ]
     */
    private static function compareAssoc(array $value1 = null, array $value2 = null)
    {
        $result = [];
        if (!isset($value1) && !isset($value2)) {
            return [];
        }
        if (isset($value1)) {
            foreach ($value1 as $key => $value) {
                $compare = self::compare($value, $value2[$key] ?? null);
                if (count($compare) > 0) {
                    $result[$key] = $compare;
                }
            }
        }
        if (isset($value2)) {
            foreach ($value2 as $key => $value) {
                $compare = self::compare($value1[$key] ?? null, $value);
                if (count($compare) > 0) {
                    $result[$key] = $compare;
                }
            }
        }
        return $result;
    }

    /**
     * Compare two values as string or as arrays
     *
     * @param {} $value1 Old value
     * @param {} $value2 New value
     * @return array Empty array if values are equals or ['from' => old value, 'to' => new value]
     */
    private static function compareSimpleValues($value1, $value2): array
    {
        if (is_array($value1) && is_array($value2)) {
            if ($value1 === $value2) {
                return [];
            }
        } elseif (!is_array($value1) && !is_array($value2) && strval($value1) === strval($value2)) {
            return [];
        }
        if (is_string($value1) && is_string($value2)) {
            $value1 = $value1 === '' ? null : $value1;
            $value2 = $value2 === '' ? null : $value2;
        }
        return [
            'from' => $value1,
            'to'   => $value2,
        ];
    }

    /**
     * Return values type, one of constant DataComparer::TYPES_*
     *
     * @param $value1
     * @param $value2
     * @return int
     */
    private static function getTypes($value1, $value2): int
    {
        if (!isset($value1) && !isset($value2)) {
            return self::TYPES_NULLS;
        }

        if (isset($value1) && isset($value2)) {
            if (gettype($value1) !== gettype($value2)) {
                return self::TYPES_OTHER;
            }
            if (is_array($value1)) {
                if (isAssoc($value1) || isAssoc($value2)) {
                    return self::TYPE_ASSOC_ARRAYS;
                } else {
                    return self::TYPE_SIMPLE_ARRAYS;
                }
            } else {
                return self::TYPE_SIMPLE_VALUE;
            }
        } else {
            $notNull = self::getFirstNotNull($value1, $value2);
            if (is_array($notNull)) {
                return self::TYPE_SIMPLE_ARRAYS;
            } else {
                return self::TYPE_SIMPLE_VALUE;
            }
        }
    }

    /**
     * Return first not null value or null if all values is null
     *
     * @param $value1
     * @param $value2
     * @return mixed
     */
    private static function getFirstNotNull($value1, $value2)
    {
        return isset($value1) ? $value1 : $value2;
    }
}
