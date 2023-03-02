<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inventcorp\SxopeAccessControlClient\Helpers\AccessControlHelper;
use Throwable;
use Validator;

/**
 * Class SimpleAjaxDemoController.
 *
 * @package App\Http\Controllers
 */
class SimpleAjaxDemoController extends Controller
{
    /** @var string */
    protected $pageTitle = 'SimpleAjax demo';

    /**
     * Show main page of tools.
     *
     * @return View
     */
    public function index()
    {
        activityOpenPage($this->pageTitle);

        return view(
            'demo.simple-ajax',
            [
                'pageTitle' => $this->pageTitle,
            ]
        );
    }

    /**
     * Demo of simple ajax handler.
     *
     * @param Request $request
     * @param string  $possibleUrlParam
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function demo(Request $request, $possibleUrlParam)
    {
        if (AccessControlHelper::hasActionAccess('execute') === false) {
            return $this->buildSimpleJsonError('Access Denied.');
        }

        activity("api call", "SimpleAjax demo");
        try {
            if (!$possibleUrlParam) {
                throw new Exception("possibleUrlParam is required");
            }
            $data = $request->all();
            $validator = Validator::make($data, [
                'type' => 'required|string|in:success_call,error_call',
            ]);

            if ($validator->fails()) {
                $errors = "Validation errors :";

                collect($validator->errors())->each(function ($error, $key) use (&$errors, &$fieldsErrors) {
                    $errors .= "Field {$key}, error : {$error[0]}\n";
                });

                return $this->buildSimpleJsonError($errors);
            }
            $type = $data['type'];
            if ($type == 'success_call') {
                return $this->buildSimpleJsonSuccess(['type' => $type], 'Success call notification message from server');
            } else {
                return $this->buildSimpleJsonError("Error call notification message from server");
            }
        } catch (Throwable $e) {
            activity('api error', 'Internal error occurred');
            logException($e);

            return $this->buildSimpleJsonError('Exception ' . getSanitizedExceptionMessage($e));
        }
    }
}
