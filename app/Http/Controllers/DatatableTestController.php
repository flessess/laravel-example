<?php

namespace App\Http\Controllers;

use App\DataTables\Test\DataTableResponsiveTestModelTable;
use App\DataTables\Test\DatatableTestDataTable;
use App\DataTables\Test\DatatableTestModelEditor;
use App\DataTables\Test\DataTableWideTestModelTable;
use Inventcorp\SxopeAccessControlClient\Helpers\AccessControlHelper;

/**
 * Class DatatableTestModelController.
 *
 * @package App\Http\Controllers
 */
class DatatableTestController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(DatatableTestDataTable $dataTable)
    {
        $pageTitle = 'Datatable test';

        activityOpenPage($pageTitle);

        if (in_array($dataTable->request()->get('action'), $dataTable->getActions()) &&
            AccessControlHelper::hasActionAccess('export') === false
        ) {
            AccessControlHelper::throwDatatablesAccessDenied();
        }

        return $dataTable->render('data-table-test.test', compact('pageTitle'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function wideIndex(DataTableWideTestModelTable $dataTable)
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function responsiveIndex(DataTableResponsiveTestModelTable $dataTable)
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
     * Implements fully vue based datatables page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function vueIndex(DatatableTestDataTable $dataTable)
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
