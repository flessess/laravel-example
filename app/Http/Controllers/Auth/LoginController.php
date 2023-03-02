<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Show login/re-login form
     */
    public function showLoginForm()
    {
        if (!session()->has('url.intended')) {
            $prev = url()->previous();
            session(
                ['url.intended' => ($prev && ($prev != route('login.form'))) ? $prev : $this->redirectTo]
            );
        } else {
            session(['url.intended' => $this->redirectTo]);
        }

        $backUrl = request('back_url') ?? session('url.intended');
        $formAction = config('sso.redirect_url') . $backUrl;

        if (
            Auth::check() &&
            (Str::startsWith($backUrl, '/') || Str::startsWith($backUrl, config('app.url') . '/'))
        ) {
            // if already logged in, just redirect
            return redirect($backUrl);
        }

        return view(
            'auth.login',
            [
                'pageTitle' => 'Login',
                'formAction' => $formAction,
                'emailField' => 'email',
            ]
        );
    }

    public function logout(Request $request): RedirectResponse
    {
        $reason = $request->input('reason');
        session(['logoutReason' => $reason]);

        // try to invalidate token
        $sessionParamName = config('sso.session_token_param_name');
        $token = $request->session()->get($sessionParamName);
        if ($token) {
            $client = new Client([
                'handler' => traceGuzzleHttpHandler(),
            ]);
            $client->get(config('sso.invalidate_api'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
                'verify' => config('sso.verify_certificates'),
                'allow_redirects' => false,
            ]);
        }

        $this->guard()->logout();
        $request->session()->invalidate();

        if ($reason) {
            $request->session()->put(
                'status',
                strtr($reason, [
                    'inactivity' => 'Your session was terminated due to inactivity',
                    'relogin' => 'User was switched',
                ])
            );
        }

        // if user clicked on logout link then explicitly logout from sso
        if ($request->isMethod('get')) {
            $route = config('sso.logout_redirect_url')
                . '?source_app=' . URL::to('/');

            return redirect()->intended($route);
        }

        return redirect()->route('login.form', ['back_url' => url()->previous()]);
    }

    protected function guard()
    {
        return Auth::guard(User::GUARD_NAME);
    }
}
