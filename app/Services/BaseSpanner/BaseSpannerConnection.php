<?php

namespace App\Services\BaseSpanner;

use App\Services\BaseSpanner\Query\SpannerQueryBuilder;
use App\Services\BaseSpanner\Query\SpannerQueryGrammar;
use App\Services\BaseSpanner\Schema\BaseSpannerSchemaBuilder;
use App\Services\BaseSpanner\Schema\BaseSpannerSchemaGrammar;
use App\Services\BaseSpanner\SpannerBinaryUuid;
use Closure;
use Colopl\Spanner\Connection as BaseConnection;
use Debugbar;
use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\Session\SessionPoolInterface;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Str;
use Psr\Cache\CacheItemPoolInterface;
use Ramsey\Uuid\Uuid;
use RedisException;

/**
 * Support for reconnecting of expired sessions
 */
class BaseSpannerConnection extends BaseConnection
{
    /**
     * Create a new spanner database connection instance.
     *
     * @param string $instanceId instance ID
     * @param string $databaseName
     * @param string $tablePrefix
     * @param array $config
     * @param CacheItemPoolInterface $authCache
     * @param SessionPoolInterface $sessionPool
     */
    public function __construct(
        string $instanceId,
        string $databaseName,
        $tablePrefix = '',
        array $config = [],
        CacheItemPoolInterface $authCache = null,
        SessionPoolInterface $sessionPool = null,
    ) {
        parent::__construct($instanceId, $databaseName, $tablePrefix, $config, $authCache, $sessionPool);
    }

    /**
     * Wraps runQueryCallback with profiling queries via newrelic
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  Closure  $callback
     * @return mixed
     */
    protected function runQueryCallbackProfiled($query, $bindings, Closure $callback)
    {
        if (extension_loaded('newrelic')) { // Ensure PHP agent is available
            $result = newrelic_record_datastore_segment(function () use ($query, $bindings, $callback) {
                return parent::runQueryCallback($query, $bindings, $callback);
            },
            [
                'product'      => 'MySQL',
                'host'         => '',
                'portPathOrId' => $this->getDatabaseName(),
                'databaseName' => $this->getDatabaseName(),
                'query'        => $query,
            ]);
            return $result;
        }
        return parent::runQueryCallback($query, $bindings, $callback);
    }

    /**
     * Handle "session not found" errors
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  Closure  $callback
     * @return mixed
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        return $this->runQueryCallbackProfiled($query, $bindings, $callback);
    }

    protected static $spannerEmulatorLock = null;

    /**
     * @template T
     * @param  Closure(static): T $callback
     * @param  int $attempts
     * @return T
     */
    public function transaction(Closure $callback, $attempts = Database::MAX_RETRIES)
    {
        $hasSpannerEmulator = $this->config['client']['hasEmulator'] ?? false;
        // ignore nested transactions and real spanner
        if ($this->transactions > 0 || !$hasSpannerEmulator) {
            return traceInSpan('sql-transaction', function () use ($callback, $attempts) {
                return parent::transaction($callback, $attempts);
            });
        }

        try {
            if (is_null(self::$spannerEmulatorLock)) {
                // access redis without prefix
                $redis = app('redis');
                $store = new RedisStore($redis, '', 'default');
                self::$spannerEmulatorLock = $store->lock('spanner-emulator_transaction_lock', 300);
            }

            $measure = 'spanner-emulator-lock';
            Debugbar::startMeasure($measure, 'Spanner Emulator Lock');
            return self::$spannerEmulatorLock->block(30, function () use ($callback, $attempts, $measure) {
                Debugbar::stopMeasure($measure);
                return traceInSpan('sql-transaction', function () use ($callback, $attempts) {
                    return parent::transaction($callback, $attempts);
                });
            });
        } catch (RedisException $e) {
            // error with redis, just call transaction directly
            return traceInSpan('sql-transaction', function () use ($callback, $attempts) {
                return parent::transaction($callback, $attempts);
            });
        }
    }

    public function query(): SpannerQueryBuilder
    {
        $queryGrammar = $this->getQueryGrammar();
        assert($queryGrammar instanceof SpannerQueryGrammar);
        return new SpannerQueryBuilder($this, $queryGrammar, $this->getPostProcessor());
    }

    public function getSchemaBuilder(): BaseSpannerSchemaBuilder
    {
        if ($this->schemaGrammar === null) {
            $this->useDefaultSchemaGrammar();
        }

        return new BaseSpannerSchemaBuilder($this);
    }

    protected function getDefaultQueryGrammar(): SpannerQueryGrammar
    {
        return new SpannerQueryGrammar();
    }

    protected function getDefaultSchemaGrammar(): BaseSpannerSchemaGrammar
    {
        return new BaseSpannerSchemaGrammar();
    }

    /**
     * Prepare the query bindings for execution.
     *
     * Supports optional conversion of all uuids into bytes
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $bindings = parent::prepareBindings($bindings);

        $uuidCasts = $this->getConfig('convert_string_uuids_params_to_bytes_in_queries');
        $hexStringUuidCasts = $this->getConfig('convert_hex_string_len_32_params_to_bytes_in_queries');

        if ($uuidCasts || $hexStringUuidCasts) {
            // dynamic replacement of string uuids should be enabled per database
            // replaces all uuids params indiscriminately in case of simple queries, like
            // DB::table('')->where
            // DB::statement('', [])
            foreach ($bindings as $key => $value) {
                if (is_string($value)) {
                    $strLen = strlen($value);
                    if ($uuidCasts && $strLen === 36 && Str::isUuid($value)) {
                        $bindings[$key] = new SpannerBinaryUuid($value);
                    }

                    // process uuids without dashes
                    if ($hexStringUuidCasts && $strLen === 32 && preg_match('/^[\da-f]{32}$/iD', $value) > 0) {
                        $bindings[$key] = new SpannerBinaryUuid(Uuid::fromString($value)->toString());
                    }
                }
            }
        }

        return $bindings;
    }

    /**
     * Execute a Closure with modified config flag.
     */
    protected function executeClosureWithModifiedConfig(Closure $callback, string $flagName, bool $flagValue): mixed
    {
        $savedFlagValue = $this->getConfig($flagName) == true;
        try {
            $this->config[$flagName] = $flagValue;
            return $callback();
        } finally {
            $this->config[$flagName] = $savedFlagValue;
        }
    }

    /**
     * Execute a Closure without replacement of string uuids to binary uuids.
     */
    public function withoutUuidCasts(Closure $callback): mixed
    {
        return $this->executeClosureWithModifiedConfig(
            $callback,
            'convert_string_uuids_params_to_bytes_in_queries',
            false,
        );
    }

    /**
     * Execute a Closure with replacement of string uuids to binary uuids.
     */
    public function withUuidCasts(Closure $callback): mixed
    {
        return $this->executeClosureWithModifiedConfig(
            $callback,
            'convert_string_uuids_params_to_bytes_in_queries',
            true,
        );
    }

    /**
     * Execute a Closure without replacement of string uuids without dashes (hex string with len 32) to binary uuids.
     */
    public function withoutHexStringUuidCasts(Closure $callback): mixed
    {
        return $this->executeClosureWithModifiedConfig(
            $callback,
            'convert_hex_string_len_32_params_to_bytes_in_queries',
            false,
        );
    }

    /**
     * Execute a Closure with replacement of string uuids without dashes (hex string with len 32) to binary uuids.
     */
    public function withHexStringUuidCasts(Closure $callback): mixed
    {
        return $this->executeClosureWithModifiedConfig(
            $callback,
            'convert_hex_string_len_32_params_to_bytes_in_queries',
            true,
        );
    }

    /**
     * Execute a Closure without any replacement of string uuids to binary uuids.
     */
    public function withoutAnyUuidCasts(Closure $callback): mixed
    {
        return $this->withoutUuidCasts(
            function () use ($callback) {
                return $this->withoutHexStringUuidCasts($callback);
            }
        );
    }

    /**
     * Execute a Closure with replacement of string uuids to binary uuids.
     */
    public function withAnyUuidCasts(Closure $callback): mixed
    {
        return $this->withUuidCasts(
            function () use ($callback) {
                return $this->withHexStringUuidCasts($callback);
            }
        );
    }
}
