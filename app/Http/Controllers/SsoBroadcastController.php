<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Broadcast;

/**
 * Custom broadcast auth controller, used by sso pusher channel
 */
class SsoBroadcastController extends Controller
{
    /**
     * Authenticate the request for channel access.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        if ($request->hasSession()) {
            $request->session()->reflash();
        }

        return Broadcast::connection(config('sso.broadcast_connection'))->auth($request);
    }
}
