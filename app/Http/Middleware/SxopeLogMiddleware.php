<?php

namespace App\Http\Middleware;

use App\Services\SxopeLogService;
use Closure;
use Exception;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Response;

/**
 * Class SxopeLogMiddleware.
 *
 * @package Illuminate\Auth\Middleware
 */
class SxopeLogMiddleware extends Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string[] ...$guards
     *
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $response = null;
        try {
            $response = $next($request);
        } finally {
            if (config('sxope-logging.enabled')) {
                try {
                    SxopeLogService::logRequest($request);
                } catch (Exception $e) {
                    logException($e, 'SxopeLogService::logRequest error');
                }
            }
        }

        if (defined('LARAVEL_START')) {
            if ($response instanceof Response) {
                $response->header('x-backend-time', round(microtime(true) - LARAVEL_START, 3));
            } else {
                $response->headers->set('x-backend-time', round(microtime(true) - LARAVEL_START, 3));
            }
        }

        return $response;
    }
}
