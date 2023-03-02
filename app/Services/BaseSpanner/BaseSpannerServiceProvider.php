<?php

namespace App\Services\BaseSpanner;

use App\Services\BaseSpanner\BaseSpannerConnection;
use App\Services\BaseSpanner\BaseSpannerSessionLock;
use App\Services\BaseSpanner\Debugbar\SpannerLaravelDebugbar;
use Barryvdh\Debugbar\LaravelDebugbar;
use Barryvdh\Debugbar\SymfonyHttpDriver;
use Colopl\Spanner\SpannerServiceProvider;
use Illuminate\Database\DatabaseManager;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Redis;
use Google\Cloud\Spanner\Session\CacheSessionPool;
use Google\Cloud\Spanner\Session\SessionPoolInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;

/**
 * Registers 'base-spanner' driver
 * Name of connection in 'databases.php' should not be name of any other driver to work
 */
class BaseSpannerServiceProvider extends SpannerServiceProvider
{
    protected string $authHash;
    protected string $sessionPoolDriver;

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->resolving('db', function (DatabaseManager $db) {
            $db->extend('base-spanner', function ($config, $name) {
                return $this->createSpannerConnection($this->parseConfig($config, $name));
            });
        });

        // debugbar with support of BYTES()
        $this->app->singleton(LaravelDebugbar::class, function () {
            $debugbar = new SpannerLaravelDebugbar($this->app);

            if ($this->app->bound(SessionManager::class)) {
                $sessionManager = $this->app->make(SessionManager::class);
                $httpDriver = new SymfonyHttpDriver($sessionManager);
                $debugbar->setHttpDriver($httpDriver);
            }

            return $debugbar;
        });
    }

    protected function createSpannerConnection(array $config): BaseSpannerConnection
    {
        if (config('session.driver') != 'redis' || !file_exists(config('app.google_application_credentials')) || !extension_loaded('redis')) {
            // if no credentials (artisan run outside k8) then disable redis provider
            $this->sessionPoolDriver = 'file';
        } else {
            $this->sessionPoolDriver = $config['sessionPoolDriver'] ?? 'file';
        }

        if ($this->sessionPoolDriver == 'redis') {
            $this->authHash = $this->getAuthHash("{$config['client']['projectId']}.{$config['instance']}.{$config['database']}");
        }
        $authCache = $this->createAuthCache();
        $sessionPool = $this->createSessionPool($config['session_pool'] ?? []);
        return new BaseSpannerConnection(
            $config['instance'],
            $config['database'],
            $config['prefix'],
            $config,
            $authCache,
            $sessionPool,
        );
    }

    /**
     * Hashes credentials file
     */
    protected function getAuthHash(string $database): string
    {
        return $database . '.' . sha1_file(config('app.google_application_credentials'));
    }

    /**
     * @param array $sessionPoolConfig
     * @return SessionPoolInterface
     */
    protected function createSessionPool(array $sessionPoolConfig): SessionPoolInterface
    {
        if ($this->sessionPoolDriver != 'redis') {
            return parent::createSessionPool($sessionPoolConfig);
        }
        $cachePrefix = "spanner.session.{$this->authHash}";
        $store = new RedisStore(Redis::connection()->client());
        $factory = new LockFactory($store);

        $lock = $factory->createLock("{$cachePrefix}.lock");
        $lock = new BaseSpannerSessionLock($lock);

        return new CacheSessionPool(
            new RedisAdapter(Redis::connection()->client(), $cachePrefix),
            [ 'lock' => $lock] + $sessionPoolConfig
        );
    }

    /**
     * @return CacheItemPoolInterface
     */
    protected function createAuthCache(): CacheItemPoolInterface
    {
        if ($this->sessionPoolDriver != 'redis') {
            return parent::createAuthCache();
        }
        $cachePrefix = "spanner.auth.{$this->authHash}";
        return new RedisAdapter(Redis::connection()->client(), $cachePrefix);
    }
}
