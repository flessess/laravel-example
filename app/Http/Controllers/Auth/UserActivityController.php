<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AuthenticateWithSSO;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * Class UserActivityController.
 *
 * @package App\Http\Controllers\Auth
 */
class UserActivityController extends Controller
{
    /**
     * @return mixed
     */
    public function tick()
    {
        return session('expire_at');
    }

    /**
     * Refresh session, don't send event, used as jsonp handler
     * see AuthenticateWithSSO
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function tickSSO(Request $request)
    {
        if (config('sso.enabled') && Auth::check()) {
            // tick_sso bypass middleware, so we need to update session from here
            AuthenticateWithSSO::updateSession($request);
        }

        $response = response()
            ->json([
                'expireAt' => session('expire_at'),
                'isActive' => Auth::check(),
            ]);

        if ($request->has('callback')) {
            $response->setCallback($request->input('callback'));
        }

        // enable CORS
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET');

        return $response;
    }
}
