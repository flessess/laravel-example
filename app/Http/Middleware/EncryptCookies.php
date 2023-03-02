<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        'sidebar-state', // set by js
        'INVENTCORP_DEBUG_SSO_JS', //debug sso
        'INVENTCORP_DEBUG_JS', //debug js
    ];

    /**
     * Create a new CookieGuard instance.
     *
     * @param \Illuminate\Contracts\Encryption\Encrypter $encrypter
     *
     * @return void
     */
    public function __construct(EncrypterContract $encrypter)
    {
        $this->except[] = config('sso.cookie'); // used to logout only selected browser
        $this->except[] = config('sso.session_lifetime_cookie'); // used to pass session lifetime to apps
        $this->except[] = config('sso.user_id_cookie'); // used to track user id changes
        $this->except[] = config('sso.data_owner_id_cookie'); // used to track data owner id changes
        parent::__construct($encrypter);
    }
}
