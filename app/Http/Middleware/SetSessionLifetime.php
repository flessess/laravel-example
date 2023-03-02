<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Cookie;

/**
 * Set session lifetime based on cookie
 */
class SetSessionLifetime
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, $next)
    {
        $cookieName = config('sso.session_lifetime_cookie');
        $lifetime = (int)Cookie::get($cookieName, 0);
        if ($lifetime > 0) {
            config(['session.lifetime' => $lifetime]);
        }
        return $next($request);
    }

}
