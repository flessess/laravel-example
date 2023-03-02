<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Attributes\Schemas\Models\V2\MasterOutbox\FileEntityWithAllowed;
use App\Http\Attributes\Schemas\Models\V2\MasterOutbox\FileEntity;
use App\Http\Attributes\Schemas\Requests\V2\MasterOutbox\FileCreateRequest as FileCreateRequestSchema;
use App\Http\Attributes\Schemas\Requests\V2\MasterOutbox\FileUpdateRequest as FileUpdateRequestSchema;
use App\Http\Requests\Api\V2\MasterOutbox\FileCreateRequest;
use App\Http\Requests\Api\V2\MasterOutbox\FileGetCountsRequest;
use App\Http\Requests\Api\V2\MasterOutbox\FileGetListRequest;
use App\Http\Requests\Api\V2\MasterOutbox\FileUpdateRequest;
use App\Services\MasterOutbox\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Parameters\Collections\Pagination;
use Sxope\Http\Attributes\Schemas\Operations\SxopeDelete;
use Sxope\Http\Attributes\Schemas\Operations\SxopeGet;
use Sxope\Http\Attributes\Schemas\Operations\SxopePatch;
use Sxope\Http\Attributes\Schemas\Operations\SxopePost;
use Sxope\Http\Attributes\Schemas\Requests\CountsRequest;
use Sxope\Http\Attributes\Schemas\Requests\FormDataRequestBody;
use Sxope\Http\Attributes\Schemas\Requests\SearchRequest;
use Sxope\Http\Attributes\Schemas\Responses\CountsResponse;
use Sxope\Http\Attributes\Schemas\Responses\EntityListResponse;
use Sxope\Http\Attributes\Schemas\Responses\EntityResponse;
use Sxope\Http\Attributes\Schemas\Responses\Error404Response;
use Sxope\Http\Attributes\Schemas\Responses\Error422Response;
use Sxope\Http\Attributes\Schemas\Responses\Error500Response;
use Sxope\Http\Attributes\Schemas\Responses\OkResponse;
use Sxope\ValueObjects\Id;

class FilesController extends BaseController
{
    #[SxopePost(
        path: '/api/v2/master-outbox/files',
        operationId: 'master-outbox-files-create-v2',
        description: 'Create files',
        security: ['ApiKeyAuth' => []],
        requestBody: new FormDataRequestBody(FileCreateRequestSchema::class),
        tags: ['Upload API - v2 - Master Outbox Cards'],
        responses: [
            [EntityResponse::class, FileEntity::class, '2.0'],
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function create(FileCreateRequest $request, FileService $fileService): JsonResponse
    {
        return $this->respondSuccess($fileService->create($request->toDto()));
    }

    #[SxopePatch(
        path: '/api/v2/master-outbox/files/{fileId}',
        operationId: 'master-outbox-files-update-v2',
        description: 'Update file',
        summary: 'Update file',
        security: ['ApiKeyAuth' => []],
        requestBody: new FormDataRequestBody(FileUpdateRequestSchema::class),
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'fileId', name: 'fileId', description: 'File id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            ),
        ],
        responses: [
            [EntityResponse::class, FileEntity::class, '2.0'],
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function update(string $fileId, FileUpdateRequest $request, FileService $fileService): JsonResponse
    {
        return $this->respondSuccess($fileService->update(Id::create($fileId), $request->toDto()));
    }

    #[SxopePost(
        path: '/api/v2/master-outbox/files/list',
        operationId: 'master-outbox-files-list-v2',
        description: 'Files list',
        security: ['ApiKeyAuth' => []],
        requestBody: [SearchRequest::class, FileGetListRequest::class],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: new Pagination(Pagination::SCOPE_PAGE),
        responses: [
            [EntityListResponse::class, FileEntity::class, '2.0'],
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ],

    )]
    public function list(FileGetListRequest $request, FileService $fileService): JsonResponse
    {
        return $this->respondSuccess($fileService->getList($request->getSearchConditions()));
    }

    #[SxopePost(
        path: '/api/v2/master-outbox/files/counts',
        operationId: 'master-outbox-files-counts-v2',
        description: 'Files counts',
        security: ['ApiKeyAuth' => []],
        requestBody: [CountsRequest::class, FileGetCountsRequest::class, FileGetCountsRequest::class],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        responses: [
            [CountsResponse::class, FileGetCountsRequest::class, '2.0'],
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ],

    )]
    public function counts(FileGetCountsRequest $request, FileService $fileService): JsonResponse
    {
        return $this->respondSuccess($fileService->getCounts($request->getSearchConditions()));
    }

    #[SxopeDelete(
        path: '/api/v2/master-outbox/files/{fileId}',
        operationId: 'master-outbox-files-delete-v2',
        description: 'Delete file',
        summary: 'Delete file',
        security: ['ApiKeyAuth' => []],
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
    public function delete(string $fileId, FileService $fileService): JsonResponse
    {
        $fileService->delete(Id::create($fileId));
        return $this->respondSuccess([]);
    }

    #[SxopeGet(
        path: '/api/v2/master-outbox/files/{fileId}/download',
        operationId: 'master-outbox-files-download-attachment-v2',
        description: 'Download file',
        security: ['ApiKeyAuth' => []],
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
        ],
    )]
    public function downloadAttachment(string $fileId, FileService $fileService): \Illuminate\Http\Response
    {
        $data = $fileService->getFileEncodedContent(Id::create($fileId));

        return Response::make(
            $data->fileContent,
            200,
            [
                'Content-Type' => $data->mimeType,
            ]
        );
    }

    #[SxopeGet(
        path: '/api/v2/master-outbox/files/{fileId}',
        operationId: 'master-outbox-file-get-v2',
        description: 'Get file',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v2 - Master Outbox Cards'],
        parameters: [
            new Parameter(
                parameter: 'fileId', name: 'fileId', description: 'File id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            )
        ],
        responses: [
            new EntityResponse(FileEntityWithAllowed::class, '2.0'),
            new Error404Response(),
            new Error422Response(),
            new Error500Response(),
        ]
    )]
    public function get(string $fileId, FileService $fileService): JsonResponse
    {
        return $this->respondSuccess($fileService->findById(Id::create($fileId)));
    }
}
