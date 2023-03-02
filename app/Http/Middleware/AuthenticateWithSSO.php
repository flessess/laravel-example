<?php

namespace App\Http\Middleware;

use App\Events\SsoSessionExpireAtUpdatedEvent;
use App\Helpers\ChannelHelper;
use App\Helpers\RequestHelper;
use App\Services\SxopeApiService;
use App\Services\SxopeIdService;
use Closure;
use Exception;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

/**
 * Class AuthenticateWithSSO.
 *
 * @package Illuminate\Auth\Middleware
 */
class AuthenticateWithSSO extends Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string[]                 ...$guards
     *
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // check if user agent supported
        $agent = new Agent();
        $supported = $agent->isRobot()
            || $agent->isDesktop()
            || $agent->isiPad()
            || $agent->isiPhone()
            || $agent->isMobile();

        $requestUserAgent = request()->header('user-agent');
        $isDP = (bool) preg_match('#dp desktop#i', $requestUserAgent);

        if (!$supported && !$isDP && !$request->ajax()) {
            throw new HttpResponseException(
                response(view('errors.device-not-supported', ['disableSupportButton' => true]))
            );
        }

        // check if user agent supported
        $agent = new Agent();
        $supported = $agent->isRobot()
            || $agent->isDesktop()
            || $agent->isiPad()
            || $agent->isiPhone()
            || $agent->isMobile();

        $requestUserAgent = request()->header('user-agent');
        $isDP = (bool) preg_match('#dp desktop#i', $requestUserAgent);

        if (!$supported && !$isDP && !$request->ajax()) {
            throw new HttpResponseException(
                response(view('errors.device-not-supported', ['disableSupportButton' => true]))
            );
        }

        $tokenParamName = config('sso.token_url_param');

        $token = '';
        $currentUrlWithoutToken = $request->fullUrl();
        if ($request->has($tokenParamName)) {
            $token = $request->$tokenParamName;
            $newQuery = $request->query();
            unset($newQuery[$tokenParamName]); //remove token param
            $question = $request->getBaseUrl() . $request->getPathInfo() === '/' ? '/?' : '?';
            $currentUrlWithoutToken = count($newQuery) == 0
                ? $request->url()
                : $request->url() . $question . Arr::query($newQuery);
        }

        $sessionParamName = config('sso.session_token_param_name');
        $sessionValidationTimeParamName = config('sso.session_validation_time_param_name');
        $sessionRevalidationInterval = config('sso.session_token_revalidation_interval');

        $userIdChanged = false;
        $ssoUserIdCookie = $request->cookie(config('sso.user_id_cookie'));
        if ($ssoUserIdCookie && Auth::check()) {
            $ssoIds = explode('.', $ssoUserIdCookie);
            $userId = Auth::user()->{config('sso.user.user_id')};
            $originalUserInfo = session(config('sso.original_user.session_key'));
            $originalUserId = $originalUserInfo[config('sso.original_user.session_keys.user_id')] ?? null;
            if (
                ($ssoIds[0] ?? null) !== $userId ||
                ($ssoIds[1] ?? null) !== $originalUserId ||
                $request->cookie(config('sso.data_owner_id_cookie')) !== (string) Auth::user()->data_owner_id
            ) {
                $userIdChanged = true;
            }
        }

        $lastValidationTime = 0;
        $needTokenCheck = true;
        if (!$token) {
            $token = $request->session()->get($sessionParamName);
            $lastValidationTime = (int) $request->session()->get($sessionValidationTimeParamName);
            if ($sessionRevalidationInterval && $lastValidationTime && (time() - $sessionRevalidationInterval) < $lastValidationTime) {
                // token already checked
                $needTokenCheck = false;
            }
        }

        if ($token &&
            !$userIdChanged &&
            (!$needTokenCheck || $this->checkToken($token))
        ) {
            if ($needTokenCheck) {
                session([
                    $sessionParamName => $token,
                    $sessionValidationTimeParamName => time(),
                ]);
            }

            self::updateSession($request);

            if ($request->has($tokenParamName)) {
                if (config('sso.gateway_client')) {
                    // receive gateway token
                    $sxopeApiService = app(SxopeApiService::class);
                    $sxopeApiService->loginToGateway();
                }

                return redirect($currentUrlWithoutToken);
            } else {
                return $next($request);
            }
        } else {
            if (!$userIdChanged && Auth::check()) {
                logException(
                    new Exception(
                        "Enforced user logout - token not checked or not exists in session, user IP : "
                        . RequestHelper::getClientIp()
                        . ", email : "
                        . Auth::user()->email
                        . ", token is "
                        . (empty($token) ? "not set" : "set")
                        . ", need check "
                        . ($needTokenCheck ? "true" : "false")
                    )
                );
            }
            Auth::logout();
            $request->session()->invalidate();

            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
            } else {
                return redirect(config('sso.redirect_url') . $currentUrlWithoutToken);
            }
        }
    }

    /**
     * Updates session and cookies.
     *
     * @param Request $request
     *
     * @return void
     */
    public static function updateSession($request)
    {
        if (!session('webConnectionId')) {
            //have unique connection id for each connection from each browser etc
            session([
                'webConnectionId' => hash_hmac(
                    'sha256',
                    Str::random(40),
                    class_basename(Auth::user()) . '.' . Auth::user()->getAuthIdentifier()),
            ]);
        }

        // cookie used to make difference between distinct browsers
        $cookieName = config('sso.cookie');
        $cookieDomain = config('sso.cookie_domain');
        if (!$request->hasCookie($cookieName)) {
            $cookieValue = hash_hmac(
                'sha256',
                Str::random(40),
                class_basename(Auth::user()) . '.' . Auth::user()->getAuthIdentifier()
            );
        } else {
            $cookieValue = $request->cookie($cookieName);
        }

        $lifetime = config('session.lifetime');
        Cookie::queue(
            $cookieName,
            $cookieValue,
            $lifetime + 1, // slightly longer than session
            '/', // path
            $cookieDomain,
            URL::formatScheme() === 'https://', // secure
            false, // httpOnly,
            false, // raw,
            URL::formatScheme() === 'https://' ? 'None' : null //sameSite
        );

        $expireAt = now()->addMinutes($lifetime)->timestamp;
        session([
            'lifetime' => $lifetime,
            'expire_at' => $expireAt,
        ]);
        if (!($request->routeIs('tick_sso') || $request->routeIs('logout'))) {
            $ssoEvent = new SsoSessionExpireAtUpdatedEvent(
                Auth::user()->email,
                $expireAt,
                session('webConnectionId'),
                $cookieValue,
                ChannelHelper::getOriginalEmail()
            );
            App::terminating(function () use ($ssoEvent) {
                event($ssoEvent);
            });
        }
        if (!config('debugbar.inject')) {
            // for logged-in users only, doesn't have any effect on environments where debugbar is disabled
            config(['debugbar.inject' => true]);
        }
        if (!config('debugbar.clockwork')) {
            // for logged-in users only, doesn't have any effect on environments where debugbar is disabled
            config(['debugbar.clockwork' => true]);
        }
    }

    /**
     * Assigns Authenticated user.
     *
     * @param string $token
     *
     * @return bool
     */
    protected function checkToken($token)
    {
        if ($token) {
            /** @var SxopeIdService $idService */
            $idService = app(SxopeIdService::class);

            $data = $idService->getUserInfoByIdToken($token);
            if ($data === false) {
                logException(
                    new Exception(
                        "Auth server returned wrong response code, user IP " . RequestHelper::getClientIp()
                    )
                );
                return false;
            }

            $user = $idService->storeUser($data);
            if ($user === false) {
                logException(
                    new \Exception(
                        "Auth server returned empty user data or user_id, user IP " . RequestHelper::getClientIp()
                    )
                );
            }

            if (Auth::guest()) {
                Auth::loginUsingId($user->getKey());

                session([
                    config('sso.original_user.session_key') => $data['extras']['original_user'] ?? null,
                    config('sso.custom_attributes.session_key') => $data['extras']['custom_attributes'] ?? null,
                    config('sso.data_owners.session_key') => $data['extras']['data_owners'] ?? null,
                ]);
            } else {
                session([
                    config('sso.custom_attributes.session_key') => $data['extras']['custom_attributes'] ?? null,
                ]);
            }

            return true;
        }

        return false;
    }
}
