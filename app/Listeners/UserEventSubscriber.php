<?php

namespace App\Listeners;

use App\Events\SsoUserLoggedOutEvent;
use App\Helpers\ChannelHelper;
use App\Helpers\RequestHelper;
use App\Models\Audit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Cookie;

/**
 * Class UserEventSubscriber.
 *
 * @package App\Listeners
 */
class UserEventSubscriber
{
    /**
     * Handle user login events.
     *
     * @param Login $event
     */
    public function onUserLogin(Login $event)
    {
        $primaryKey = $event->user->getKey();
        $now = Carbon::now();
        $ip = RequestHelper::getClientIp();

        // stopping automatic Auditing
        User::disableAuditing();

        if ($event->user instanceof User) {
            $event->user->update([
                'last_sign_in_at' => $event->user->current_sign_in_at,
                'last_sign_in_ip' => $event->user->current_sign_in_ip,
                'current_sign_in_at' => $now,
                'current_sign_in_ip' => $ip,
                'sign_in_count' => $event->user->sign_in_count + 1,
            ]);
        }

        // restoring auditable events
        User::enableAuditing();

        $now = Carbon::now();

        $request = request();

        // manual Auditing
        activity(Audit::EVENT_LOGGED_IN, '', [
            'user_id' => $primaryKey,
            'url' => $request->fullUrl(),
            'ip_address' => RequestHelper::getClientIp(),
            'user_agent' => $request->fp_id . " " . $request->header('User-Agent'),
            'created_at' => $now,
        ]);
    }

    /**
     * Handle user logout events.
     *
     * @param Logout $event
     */
    public function onUserLogout(Logout $event)
    {
        // TODO sometimes user is null. Check it.
        if (!$event->user) {
            return;
        }

        $primaryKey = $event->user->getKey();
        $now = Carbon::now();

        activity(Audit::EVENT_LOGGED_OUT, '', [
            'user_id' => $primaryKey,
            'url' => request()->fullUrl(),
            'ip_address' => RequestHelper::getClientIp(),
            'user_agent' => request()->header('User-Agent'),
            'created_at' => $now,
        ]);

        if (session('logoutReason') !== 'relogin') {
            event(new SsoUserLoggedOutEvent(
                $event->user->email,
                session('logoutReason'),
                Cookie::get(config('sso.cookie')),
                ChannelHelper::getOriginalEmail()
            ));
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Login::class,
            'App\Listeners\UserEventSubscriber@onUserLogin'
        );

        $events->listen(
            Logout::class,
            'App\Listeners\UserEventSubscriber@onUserLogout'
        );
    }
}
