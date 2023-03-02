<?php

namespace App\Providers;

use App\Helpers\BytesHelper;
use App\Listeners\SxopeChangeLogSubscriber;
use App\Services\AccessControlSnapshotService;
use App\Services\Cache\CacheAccessorService;
use App\Services\Cache\CacheAccessorServiceInterface;
use App\Services\MasterOutbox\AllowedEntitiesService;
use App\Services\SxopeLogService\SxopeChangeLogLogger;
use Appzcoder\CrudGenerator\CrudGeneratorServiceProvider;
use Barryvdh\Debugbar\ServiceProvider as DebugbarServiceProvider;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Exception;
use Google\Cloud\PubSub\PubSubClient;
use GuzzleHttp\Client;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use InfyOm\Generator\InfyOmGeneratorServiceProvider;
use Krlove\EloquentModelGenerator\Provider\GeneratorServiceProvider;
use Laracasts\Generators\GeneratorsServiceProvider as LaracastsGeneratorsServiceProvider;
use Spatie\LaravelIgnition\IgnitionServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $headers = request()->headers;
        // check header for minikube
        if (App::environment() == 'local' && $headers->get('X-Forwarded-Proto') == 'https') {
            URL::forceScheme('https');
        }

        // re-map public path to front-end subfolder
        $this->app->bind('path.public', function () {
            return base_path() . '/public/fe';
        });

        // extends blade with @if_sidebar_closed
        // cookie set via node_modules/sxope-theme/js/sxope.js
        // by default it is open/expanded and js code detects if it should be closed/collapsed based on window width
        Blade::if('if_sidebar_closed', function () {
            return Cookie::get('sidebar-state') == 'closed' && config('app.ui_sidebar_displayed') !== false;
        });

        Blade::if('if_no_sidebar', function () {
            return config('app.ui_sidebar_displayed') === false;
        });

        \Illuminate\Database\Schema\Builder::defaultStringLength(191);

        //log filed queues
        Queue::failing(function (JobFailed $event) {
            $jobClassName = '';
            if ($event->job) {
                $jobClassName = $event->job->resolveName();
            }
            logException($event->exception, "Job '{$jobClassName}' for connection : {$event->connectionName} has been failed");
        });

        Queue::before(function (JobProcessing $event) {
            if (extension_loaded('newrelic')) { // Ensure PHP agent is available
                $jobClassName = '';
                if ($event->job) {
                    $jobClassName = $event->job->resolveName();
                }
                newrelic_end_transaction(true); // stop recording the current transaction
                newrelic_start_transaction(ini_get("newrelic.appname")); // start recording a new transaction
                newrelic_name_transaction("Job {$jobClassName}");
            }
        });
        Queue::after(function (JobProcessed $event) {
            if (extension_loaded('newrelic')) { // Ensure PHP agent is available
                newrelic_end_transaction(); // stop recording the current transaction
                newrelic_start_transaction(ini_get("newrelic.appname")); // start recording a new transaction
            }
        });

        Validator::extend('exists_bytes', function ($attribute, $value, $parameters, $validator) {
            try {
                $query = DB::table($parameters[0])
                    ->where($parameters[1], BytesHelper::getBytes($value));

                return $query->exists();
            } catch (Exception $e) {
                return false;
            }
        });

        Validator::extend('uuid_or_hex', function ($attribute, $value, $parameters, $validator) {
            try {
                return (boolean) BytesHelper::getBytes($value);
            } catch (Exception $e) {
                return false;
            }
        });

        Validator::extend('exists_integer', function ($attribute, $value, $parameters, $validator) {
            try {
                $query = DB::table($parameters[0])
                    ->where($parameters[1], (integer) $value);

                return $query->exists();
            } catch (Exception $e) {
                return false;
            }
        });

        $this->app->singleton(PubSubClient::class, function () {
            return new PubSubClient([
                'projectId' => config('pubsub.pubsub_app_events_topic_project_id'),
            ]);
        });

        $this->app->singleton(SxopeChangeLogSubscriber::class);
        $this->app->singleton(SxopeChangeLogLogger::class);
        $this->app->singleton(CacheAccessorServiceInterface::class, CacheAccessorService::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (in_array($this->app->environment(), ['local', 'staging'])) {
            $this->app->register(DebugbarServiceProvider::class);
            $this->app->register(IgnitionServiceProvider::class);
        }
        if ($this->app->environment() == 'local') {
            // $this->app->register(AdminLTETemplatesServiceProvider::class);
            $this->app->register(CrudGeneratorServiceProvider::class);
            $this->app->register(IdeHelperServiceProvider::class);
            $this->app->register(InfyOmGeneratorServiceProvider::class);
            $this->app->register(LaracastsGeneratorsServiceProvider::class);
        }

        // debugbar routes with additional middleware
        $this->loadRoutesFrom(base_path('routes/debugbar.php'));
        $this->registerFiveStarApi();
        $this->app->singleton(AccessControlSnapshotService::class);

        // debugbar routes with additional middleware
        $this->loadRoutesFrom(base_path('routes/debugbar.php'));

        // debugbar routes with additional middleware
        $this->loadRoutesFrom(base_path('routes/debugbar.php'));

        $this->app->singleton(AllowedEntitiesService::class);
    }

     /**
     * Register sphere api class in the application.
     */
    protected function registerFiveStarApi(): void
    {
        $this->app->bind(FiveStarApi::class, function () {
            $host = rtrim(config('five-star-api.host'), '/') . '/api/v1/';

            $config = [
                'base_uri' => $host,
                'verify' => config('app.verify_service_ssl'),
                'headers' => [
                    'accept' => 'application/json',
                    'content-Type' => 'application/json',
                    'x-api-key' => config('five-star-api.key'),
                ],
            ];

            $client = new Client($config);

            return new FiveStarApi($client);
        });
    }
}
