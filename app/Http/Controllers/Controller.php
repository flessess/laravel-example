<?php

namespace App\Http\Controllers;

use App\Helpers\DataTableFilterHelper;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base class for all controllers.
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Init with builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $defaultBuilder - default builder used for getting fields values
     *
     * @return \App\Helpers\DataTableFilterHelper
     */
    public function getDataTableFilterHelper($defaultBuilder = null)
    {
        return new DataTableFilterHelper($defaultBuilder);
    }

    /**
     * Returns simple-json formatted response for error with error message.
     *
     * @param string $message error message
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws Exception
     */
    protected function buildSimpleJsonError($message)
    {
        if ('' == $message) {
            throw new Exception('Message is required');
        }

        return response()->json([
            'status' => 'fail',
            'message' => $message,
        ]);
    }

    /**
     * Returns simple-json formatted response for success with optional message.
     *
     * @param string|array $result  result object
     * @param string $message optional success message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function buildSimpleJsonSuccess($result, $message = '')
    {
        $result = [
            'status' => 'success',
            'result' => $result,
        ];

        if ('' != $message) {
            $result['message'] = $message;
        }

        return response()->json($result);
    }

    /**
     * Returns simple-json formatted response for success with optional message and empty result.
     *
     * @param string $message success message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function buildSimpleJsonSuccessMessage($message)
    {
        $result = [
            'status' => 'success',
            'result' => true,
        ];

        if ('' != $message) {
            $result['message'] = $message;
        }

        return response()->json($result);
    }
}
