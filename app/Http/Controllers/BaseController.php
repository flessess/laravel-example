<?php

namespace App\Http\Controllers;

/**
 * Class BaseController.
 *
 * @package App\Http\Controllers
 */
class BaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.sso');
    }
}
