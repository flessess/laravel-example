<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SxopeLogRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $level;

    /**
     * @var array
     */
    protected $extra;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var string
     */
    protected $unixTime;

    /**
     * @var string
     */
    protected $dateTime;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        foreach($data as $name => $value) {
            if(property_exists(self::class, $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug('job running');
    }
}
