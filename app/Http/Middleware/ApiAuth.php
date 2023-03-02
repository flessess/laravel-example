<?php

namespace App\Http\Middleware;

use App\Services\GoogleTokenService;
use App\Http\Controllers\Api\V1\BaseController;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class ApiAuth
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next)
    {
        $hasApiKey = $request->headers->has('X-API-KEY');
        $hasAuthorization = $request->headers->has('Authorization') && $request->bearerToken() !== null;
        if (!$hasApiKey && !$hasAuthorization) {
            throw new AuthenticationException('X-API-KEY or Authorization header is required.');
        }

        if ($hasAuthorization) {
            app(GoogleTokenService::class)->checkTokenAccess($request->bearerToken());
        }

        if ($hasApiKey) {
            if (trim($request->header('X-API-KEY')) !== trim(config('app.api_key'))) {
                return app(BaseController::class)->respondNotAllowed(['Please provide valid X-API-KEY token']);
            }
        }

        return $next($request);
    }
}
