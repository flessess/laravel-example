<?php

namespace App\Services\BaseSpanner;

use Illuminate\Queue\QueueServiceProvider as IlluminateQueueServiceProvider;

/**
 * Support for binary uuids.
 */
class BaseSpannerQueueServiceProvider extends IlluminateQueueServiceProvider
{
    /**
     * Create a new database failed job provider.
     *
     * @param  array  $config
     * @return \Illuminate\Queue\Failed\DatabaseFailedJobProvider
     */
    protected function databaseFailedJobProvider($config)
    {
        return new BaseSpannerDatabaseFailedJobProvider(
            $this->app['db'], $config['database'], $config['table']
        );
    }
}
