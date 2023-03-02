<?php

namespace App\Http\Controllers;

use App\Services\SxopeApiService;
use Illuminate\Http\RedirectResponse;
use Inventcorp\SxopeAccessControlClient\Helpers\AccessControlHelper;

/**
 * Class HomeController.
 *
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth.sso');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect('api/documentation');
    }

    /**
     * Redirect to home route.
     *
     * @return RedirectResponse
     */
    public function main()
    {
        return redirect('api/documentation');
    }

    /**
     * Show download desktop app page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Exception
     */
    public function downloadDesktopApp()
    {
        $pageTitle = 'Download Desktop App';

        /** @var SxopeApiService $sxopeApiService */
        $sxopeApiService = app(SxopeApiService::class);
        $baseApiUrl = $sxopeApiService->getBaseUrl();
        $apiToken = $sxopeApiService->getApiToken();

        return view('sxope-desktop-landing.index', compact('pageTitle', 'baseApiUrl', 'apiToken'));
    }
}
