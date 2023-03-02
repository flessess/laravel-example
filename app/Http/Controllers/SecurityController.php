<?php

namespace App\Http\Controllers;

use App\DataTables\ActivityLogDataTable;
use App\Models\Audit;
use Inventcorp\SxopeAccessControlClient\Helpers\AccessControlHelper;

/**
 * Class SecurityController.
 *
 * @package App\User
 */
class SecurityController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function activityLog()
    {
        $dataTable = resolve(ActivityLogDataTable::class);
        $pageTitle = 'Activity Log';

        if (!request()->ajax()) {
            activity(Audit::EVENT_OPENED, $pageTitle);
        }

        if (in_array($dataTable->request()->get('action'), $dataTable->getActions()) &&
            AccessControlHelper::hasActionAccess('export') === false
        ) {
            AccessControlHelper::throwAccessDenied();
        }

        return $dataTable->render('security.activity-log', compact('pageTitle'));
    }
}
