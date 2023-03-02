<?php

namespace App\Http\Middleware;

use App\Services\OpenTelemetryService;
use Closure;
use Illuminate\Http\Request;

/**
 * Trace an incoming HTTP request
 */
class OpenTelemetryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return app(OpenTelemetryService::class)
            ->handleRequest($request, $next);
    }
}
