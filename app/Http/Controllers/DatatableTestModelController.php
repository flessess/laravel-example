<?php

namespace App\Http\Controllers;

use App\DataTables\DatatableTestModelDataTable;
use App\DataTables\DatatableTestModelEditor;
use Inventcorp\SxopeAccessControlClient\Helpers\AccessControlHelper;

/**
 * Class DatatableTestModelController.
 *
 * @package App\Http\Controllers
 */
class DatatableTestModelController extends Controller
{
    /**
     * Implements fully vue based datatables page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(DatatableTestModelDataTable $dataTable)
    {
        activityOpenPage($dataTable->getPageTitle());

        if (in_array($dataTable->request()->get('action'), $dataTable->getActions()) &&
            AccessControlHelper::hasActionAccess('export') === false
        ) {
            AccessControlHelper::throwDatatablesAccessDenied();
        }

        return $dataTable->render('datatables.vue-datatable-page');
    }

    /**
     * Implements datatable.editor callback.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editor(DatatableTestModelEditor $editor)
    {
        if (AccessControlHelper::hasActionAccess(request('action')) === false) {
            AccessControlHelper::throwAccessDenied();
        }

        return $editor->process(request());
    }
}
