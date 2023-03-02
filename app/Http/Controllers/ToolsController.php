<?php

namespace App\Http\Controllers;

use App\Jobs\ExceptionTestJob;
use App\Models\Audit;
use Exception;
use Illuminate\Http\Request;

/**
 * Class ToolsController.
 *
 * @package App\Http\Controllers\Admin
 */
class ToolsController extends Controller
{
    /** @var string */
    protected $pageTitle = 'Tools';

    public function __construct()
    {
    }

    /**
     * Show main page of tools.
     */
    public function index()
    {
        activity(Audit::EVENT_OPENED, $this->pageTitle);

        return view(
            'tools.tools',
            [
                'pageTitle' => $this->pageTitle,
            ]
        );
    }

    /**
     * Raise Exception.
     *
     * @param Request $request request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Exception
     */
    public function raiseException(Request $request)
    {
        $queue = $request->input('queue');
        if (!in_array(
            $queue, [
                'sxope-upload-api-general',
                'sxope-upload-api-notifications',
                'sxope-upload-api-sso-events',
            ])
        ) {
            throw new Exception('Please ignore. This is test exception');
        }

        dispatch((new ExceptionTestJob())->onQueue($queue));

        return redirect()->back()->with('flash.message.success', 'Exception successfuly sent.');
    }
}
