<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Attributes\Schemas\Requests\V2\MasterOutbox\MarkReadBatchRequestSchema;
use App\Http\Requests\Api\V2\MasterOutbox\MarkReadBatchRequest;
use App\Http\Requests\Api\V2\MasterOutbox\MarkReadRequest;
use App\Http\Attributes\Schemas\Requests\V2\MasterOutbox\MarkReadRequest as MarkReadRequestSchema;
use App\Services\MasterOutbox\FileService;
use App\Services\MasterOutbox\FileViewLogsService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use Sxope\Facades\Sxope;
use Sxope\Http\Attributes\Schemas\Operations\SxopePost;
use Sxope\Http\Attributes\Schemas\Requests\JsonRequestBody;
use Sxope\Http\Attributes\Schemas\Responses\Error404Response;
use Sxope\Http\Attributes\Schemas\Responses\Error422Response;
use Sxope\Http\Attributes\Schemas\Responses\Error500Response;
use Sxope\Http\Attributes\Schemas\Responses\OkResponse;
use Sxope\ValueObjects\Id;

class FileViewLogController extends BaseController
{
    #[SxopePost(
        path: '/api/v2/master-outbox/cards/{cardId}/mark-as-read',
        operationId: 'master-outbox-cards-mark-read-v2',
        description: 'Mark card read',
        security: ['ApiKeyAuth' => []],
        requestBody: [JsonRequestBody::class, MarkReadRequestSchema::class],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'cardId', name: 'cardId', description: 'Card id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            )
        ],
        responses: [
            OkResponse::class,
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ],
    )]
    public function markCardRead(
        string $cardId,
        MarkReadRequest $request,
        FileViewLogsService $fileViewLogsService,
        FileService $fileService
    ): JsonResponse {
        $fileViewLogsService->markReadBatch(
            $fileService->getFileIdsByCardId(Id::create($cardId))->all(),
            Sxope::getCurrentUserId(),
            $request->isMarkAsRead()
        );

        return $this->respondSuccess(['message' => 'ok']);
    }

    #[SxopePost(
        path: '/api/v2/master-outbox/files/{fileId}/mark-read',
        operationId: 'master-outbox-files-mark-read-v2',
        description: 'Mark file read',
        security: ['ApiKeyAuth' => []],
        requestBody: [JsonRequestBody::class, MarkReadRequestSchema::class],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'fileId', name: 'fileId', description: 'File id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            )
        ],
        responses: [
            OkResponse::class,
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ],
    )]
    public function markRead(string $fileId, MarkReadRequest $request, FileViewLogsService $service): JsonResponse
    {
        $service->markReadBatch([Id::create($fileId)], Sxope::getCurrentUserId(), $request->isMarkAsRead());
        return $this->respondSuccess(['message' => 'ok']);
    }

    #[SxopePost(
        path: '/api/v2/master-outbox/files/mark-read-batch',
        operationId: 'master-outbox-files-mark-read-batch-v2',
        description: 'Mark file read',
        security: ['ApiKeyAuth' => []],
        requestBody: [JsonRequestBody::class, MarkReadBatchRequestSchema::class],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        responses: [
            OkResponse::class,
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ],
    )]
    public function markReadBatch(MarkReadBatchRequest $request, FileViewLogsService $service): JsonResponse
    {
        $service->markReadBatch(Id::batchCreate($request->file_ids), Sxope::getCurrentUserId(), $request->is_read);
        return $this->respondSuccess(['message' => 'ok']);
    }
}
