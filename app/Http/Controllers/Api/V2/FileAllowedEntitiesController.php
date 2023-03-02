<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Attributes\Schemas\Models\V2\MasterOutbox\FileAllowedEntity;
use App\Http\Attributes\Schemas\Requests\V2\MasterOutbox\FIleAllowedEntityDeleteRequestSchema;
use App\Http\Attributes\Schemas\Requests\V2\MasterOutbox\FileAllowedEntityRequest;
use App\Http\Requests\Api\V2\MasterOutbox\FileAllowedEntitiesDeleteRequest;
use App\Http\Requests\Api\V2\MasterOutbox\FileAllowedEntitiesRequest;
use App\Services\MasterOutbox\FileAllowedEntityService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Schemas\Operations\SxopeDelete;
use Sxope\Http\Attributes\Schemas\Operations\SxopePost;
use Sxope\Http\Attributes\Schemas\Requests\JsonRequestBody;
use Sxope\Http\Attributes\Schemas\Responses\EntityListResponse;
use Sxope\Http\Attributes\Schemas\Responses\Error404Response;
use Sxope\Http\Attributes\Schemas\Responses\Error422Response;
use Sxope\Http\Attributes\Schemas\Responses\Error500Response;
use Sxope\Http\Attributes\Schemas\Responses\OkResponse;
use Sxope\ValueObjects\Id;

class FileAllowedEntitiesController extends BaseController
{
    #[SxopePost(
        path: '/api/v2/master-outbox/files/{fileId}/allowed-entities',
        operationId: 'master-outbox-file-allowed-entity-create-v2',
        description: 'Create file allowed entity',
        summary: 'Create file allowed entity',
        security: ['ApiKeyAuth' => []],
        requestBody: [JsonRequestBody::class, FileAllowedEntityRequest::class],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'fileId', name: 'fileId', description: 'File id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            ),
        ],
        responses: [
            [EntityListResponse::class, FileAllowedEntity::class, '2.0'],
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function create(
        string $fileId,
        FileAllowedEntitiesRequest $request,
        FileAllowedEntityService $service
    ): JsonResponse {
        return $this->respondSuccess($service->create(Id::create($fileId), $request->toDtos()));
    }

    #[SxopePost(
        path: '/api/v2/master-outbox/files/{fileId}/allowed-entities/delete',
        operationId: 'master-outbox-file-allowed-entity-delete-v2',
        summary: 'Delete file allowed entity',
        security: ['ApiKeyAuth' => []],
        requestBody: [JsonRequestBody::class, FIleAllowedEntityDeleteRequestSchema::class],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'fileId', name: 'fileId', description: 'File id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            ),
        ],
        responses: [
            OkResponse::class,
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function delete(
        string $fileId,
        FileAllowedEntitiesDeleteRequest $request,
        FileAllowedEntityService $service
    ): JsonResponse {
        $service->delete(Id::create($fileId), $request->file_allowed_entity_id);
        return $this->respondSuccess([]);
    }
}
