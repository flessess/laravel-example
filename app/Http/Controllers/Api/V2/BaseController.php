<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseController
{
    public const API_VERSION = '2.0';

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
