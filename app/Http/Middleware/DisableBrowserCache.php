<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Disable browser cache entirely since every call is session based
 */
class DisableBrowserCache
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'api/v1/payer-files/types',
        'api/v1/payer-files/visibility-types',
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (!$this->inExceptArray($request)) {
            $response = $this->addHeaders($response, [
                "Pragma" => "no-cache",
                "Expires" => "Fri, 01 Jan 1990 00:00:00 GMT",
                "Cache-Control" => "no-cache, must-revalidate, no-store, max-age=0, private",
            ]);
        }
        return $response;
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add an array of headers to the response.
     *
     * @param  mixed  $response
     * @param  array  $headers
     * @return mixed
     */
    public function addHeaders($response, $headers)
    {
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
