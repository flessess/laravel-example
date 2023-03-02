<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Throwable;
use Validator;

/**
 * Class ThemeDemoController.
 *
 * @package App\Http\Controllers
 */
class ThemeDemoController extends Controller
{
    /** @var string */
    protected $pageTitle = 'Theme demo';

    /**
     * Show main page of tools.
     *
     * @return View
     */
    public function index()
    {
        activityOpenPage($this->pageTitle);

        return view(
            'demo.theme',
            [
                'pageTitle' => $this->pageTitle,
            ]
        );
    }
}
