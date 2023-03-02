<?php

namespace App\Services\BaseSpanner;

use Illuminate\Database\Connection;

/**
 * Class SpannerHelper.
 *
 * Helper functions for layout and page display
 */
class BaseSpannerHelper
{
    /**
     * Timezone name for display time
     *
     * @var string
     */
    const DEFAULT_TIMEZONE = 'America/New_York';

    /**
     * Timezone name for display time
     *
     * @var string
     */
    const UTC_TIMEZONE = 'UTC';
    /**
     * @var string
     */
    const DEFAULT_TIMESTAMP_FORMAT = '%m-%d-%Y %I:%M %p';

    /**
     * Checks if it's spanner model
     *
     * @param mixed $object
     */
    public static function isSpanner($object): bool
    {
        static $spannerDrivers = [
            'base-spanner' => true,
            'sxope-spanner' => true,
            'spanner' => true
        ];

        $connection = $object;
        if (!($object instanceof Connection)) {
            $connection = $object->getConnection();
        }

        return $spannerDrivers[$connection->getDriverName()] ?? false;
    }

    /**
     * Get raw formatted query for datetime field
     */
    public static function getTimestampFormattedRawQuery(string $column): string
    {
        return 'FORMAT_TIMESTAMP("' . self::DEFAULT_TIMESTAMP_FORMAT . '", CAST(' . $column . ' AS TIMESTAMP), "' . self::DEFAULT_TIMEZONE . '")';
    }

    /**
     * Get raw formatted query for datetime field in UTC
     */
    public static function getTimestampFormattedRawQueryUTC(string $column): string
    {
        return 'FORMAT_TIMESTAMP("' . self::DEFAULT_TIMESTAMP_FORMAT . '", CAST(' . $column . ' AS TIMESTAMP), "' . self::UTC_TIMEZONE . '")';
    }
}
