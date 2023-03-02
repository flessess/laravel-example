<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Validator;

/**
 * Class BaseController
 * @package App\Http\Controllers\Api
 */
class BaseController
{
    public const API_VERSION = '1.0';

    /**
     * @param $data
     *
     * @return JsonResponse
     */
    public function respondCreated($data): JsonResponse
    {
        return response()->json(
            [
                'code' => Response::HTTP_CREATED,
                'status' => 'ok',
                'data' => $data,
                'meta' => 'api version ' .  self::API_VERSION
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * @param $data
     * @param array $headers
     *
     * @return JsonResponse
     */
    public function respondSuccess($data, array $headers = []): JsonResponse
    {
        return response()->json(
            [
                'code' => Response::HTTP_OK,
                'status' => 'ok',
                'data' => $data,
                'meta' => 'api version ' . self::API_VERSION
            ],
            Response::HTTP_OK,
            $headers
        );
    }

    /**
     * @param $errors
     * @param $errorCode
     *
     * @return JsonResponse
     */
    public function respondError($errors, $errorCode): JsonResponse
    {
        return response()->json(
            [
                'code' => $errorCode,
                'status' => 'error',
                'errors' => $errors,
                'meta' => 'api version ' . self::API_VERSION
            ],
            $errorCode
        );
    }

    /**
     * @param $data
     *
     * @return JsonResponse
     */
    public function respondNotFound($data): JsonResponse
    {
        return response()->json(
            [
                'code' => '404',
                'status' => 'error',
                'data' => $data,
                'meta' => 'api version ' .  self::API_VERSION
            ],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @param $data
     *
     * @return JsonResponse
     */
    public function respondNotAllowed($data): JsonResponse
    {
        return response()->json(
            [
                'code' => '403',
                'status' => 'Forbidden',
                'data' => $data,
                'meta' => 'api version ' .  self::API_VERSION
            ],
            Response::HTTP_FORBIDDEN
        );
    }
}
