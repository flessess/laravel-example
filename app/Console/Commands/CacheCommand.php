<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Cache\CacheService;
use Illuminate\Console\Command;

class CacheCommand extends Command
{
    protected $signature = 'cache:cache-data';
    protected $description = 'Warmup allowed entities cache';

    public function handle(CacheService $cacheService): int
    {
        $cacheService->warmUpPcps();
        $cacheService->warmupPayers();
        $cacheService->warmupUserGroups();
        $cacheService->warmupUserRoles();
        $cacheService->warmupUsers();
        $cacheService->warmupPcpGroups();

        return 0;
    }
}
