<?php

namespace App\Http\Controllers;

use App\DataTables\Test\DatatableTestDataTable;

class ChangeLogController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(DatatableTestDataTable $dataTable)
    {
        $pageTitle = 'Change Log';

        activityOpenPage($pageTitle);

        return $dataTable->render('change-log', compact('pageTitle'));
    }

}
