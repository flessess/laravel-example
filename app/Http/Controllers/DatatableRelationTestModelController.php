<?php

namespace App\Http\Controllers;

use App\DataTables\DatatableRelationTestModelDataTable;
use App\DataTables\DatatableRelationTestModelEditor;
use Inventcorp\SxopeAccessControlClient\Helpers\AccessControlHelper;

/**
 * Class DatatableRelationTestModelController.
 *
 * @package App\Http\Controllers
 */
class DatatableRelationTestModelController extends Controller
{
    /**
     * Implements fully vue based datatables page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(DatatableRelationTestModelDataTable $dataTable)
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
    public function editor(DatatableRelationTestModelEditor $editor)
    {
        if (AccessControlHelper::hasActionAccess(request('action')) === false) {
            AccessControlHelper::throwAccessDenied();
        }

        return $editor->process(request());
    }
}
