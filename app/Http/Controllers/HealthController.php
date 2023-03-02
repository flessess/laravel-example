<?php

namespace App\Http\Controllers;

use Exception;

/**
 * App health checks.
 */
class HealthController extends Controller
{
    /**
     * Web pod health check
     *
     * Check when nginx & php & redis & rabbitmq are ready to accept requests
     */
    public function webCheck()
    {
        // reduce volume of data in newrelic
        if (extension_loaded('newrelic')) { // Ensure PHP agent is available
            newrelic_ignore_transaction();
        }
        return response()->json(['status' => 'OK']);
    }

    /**
     * Generates test exception.
     *
     * @return null
     */
    public function throwException()
    {
        throw new Exception('Please ignore. This is test exception');
    }
}
