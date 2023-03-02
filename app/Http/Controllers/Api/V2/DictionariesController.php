<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Attributes\Schemas\Models\V2\MasterOutbox\DictionaryModel;
use App\Http\Controllers\Api\V1\BaseController;
use App\Services\DictionariesService;
use Illuminate\Http\JsonResponse;
use Sxope\Http\Attributes\Schemas\Operations\SxopeGet;
use Sxope\Http\Attributes\Schemas\Responses\EntityResponse;
use Sxope\Http\Attributes\Schemas\Responses\Error404Response;
use Sxope\Http\Attributes\Schemas\Responses\Error422Response;
use Sxope\Http\Attributes\Schemas\Responses\Error500Response;

class DictionariesController extends BaseController
{
    #[SxopeGet(
        path: '/api/v2/master-outbox/dictionaries/list',
        operationId: 'master-outbox-dictionaries-list-v2',
        description: 'Get dictionaries list',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v2 - Master Outbox Dictionaries'],
        responses: [
            [EntityResponse::class, DictionaryModel::class, '2.0'],
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function list(DictionariesService $dictionariesService): JsonResponse
    {
        return $this->respondSuccess($dictionariesService->all());
    }
}
