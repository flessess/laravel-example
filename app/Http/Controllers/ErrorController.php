<?php

namespace App\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class BaseController.
 *
 * @package App\Http\Controllers
 */
class ErrorController extends Controller
{
    public function index($code)
    {
        $pages = [
            'device-not-supported',
        ];

        $codes = [
            //4xx Client errors
            400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410,
            411, 412, 413, 414, 415, 416, 417, 418,
            421, 422, 423, 424, 425, 426, 428, 429,
            431,
            451,
            //5xx Server errors
            500, 501, 502, 503, 504, 505, 506, 507, 508, 509, 510, 511,
            //Unofficial codes
            419, 509, 526, 529, 598,
            //nginx
            444, 494, 495, 496, 497, 499,
            //Cloudfare
            520, 521, 522, 523, 524, 525, 526
        ];

        if (in_array($code, $pages)) {
            throw new HttpResponseException(
                response(view('errors.' . $code))
            );
        } else if (in_array($code, $codes)) {
            abort($code);
        } else {
            abort(404);
        }
    }
}
