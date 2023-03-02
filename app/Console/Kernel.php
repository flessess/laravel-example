<?php

namespace App\Console;

use App\Services\SxopeLoadEnvironmentVariables;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Run the console application.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface|null  $output
     * @return int
     */
    public function handle($input, $output = null)
    {
        if (false !== ($index = array_search(LoadEnvironmentVariables::class, $this->bootstrappers))) {
            $this->bootstrappers[$index] = SxopeLoadEnvironmentVariables::class;
        }

        return parent::handle($input, $output);
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*
         * TODO: every command should have ->sendOutputTo('/docker.stderr');
         *  so hard errors can be captured by docker/gcloud
         */

//        $schedule->command('export:remove-old-files')
//            ->timezone('America/New_York')
//            ->at('02:00')
//            ->sendOutputTo('/docker.stderr');
//
//        // reload cache for maintenance notification
//        $schedule
//            ->command('notifications:maintenance')
//            ->everyFiveMinutes()
//            ->sendOutputTo('/docker.stderr');
//
//        if (config('pubsub.pubsub_enabled') && config('pubsub.pubsub_pull_enabled')) {
//            // pull events
//            $schedule
//                ->command('pubsub:listen')
//                ->cron(config('pubsub.pull_frequency'))
//                ->sendOutputTo('/docker.stderr');
//        }

        if (app()->environment(['production', 'staging'])) {
            $schedule->command('cache:cache-data')
                ->hourly()
                ->sendOutputTo('/docker.stderr');

            $schedule->command('sxope-pubsub-listener:run')
                ->everyMinute()
                ->sendOutputTo('/docker.stderr');
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
