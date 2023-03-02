<?php

namespace App\Http\Controllers\Api\V1;

use App\Api\DataApi\Facades\DataApiClientFacade;
use App\Helpers\BytesHelper;
use App\Helpers\EloquentHelper;
use App\Helpers\PageHelper;
use App\Http\Attributes\Schemas\Models\V1\MasterOutboxFileEntityTypeSchema;
use App\Http\Attributes\Schemas\Models\V1\MasterOutboxFilesAggregatedSchema;
use App\Http\Attributes\Schemas\Models\V1\MasterOutboxFileSchema;
use App\Http\Attributes\Schemas\Models\V1\MasterOutboxFileTypeSchema;
use App\Http\Attributes\Schemas\Models\V1\MasterOutboxFileVisibilityTypeSchema;
use App\Http\Attributes\Schemas\Requests\V1\MasterOutboxFileRequestSchema;
use App\Http\Attributes\Schemas\Requests\V1\MasterOutboxFileUpdateRequestSchema;
use App\Http\Attributes\Schemas\Requests\V1\MasterOutboxGetFilesListRequestParameters;
use App\Http\Attributes\Schemas\Responses\V1\GetFilesDataResponseSchema;
use App\Http\Requests\Api\V1\MasterOutboxFileCreateRequest;
use App\Http\Requests\Api\V1\MasterOutboxFilesGetRequest;
use App\Http\Requests\Api\V1\MasterOutboxFileUpdateRequest;
use App\Http\Requests\Api\V1\MasterOutboxFileUpdateViewStatusRequest;
use App\Models\DataOwner;
use App\Models\Day;
use App\Models\File;
use App\Models\MasterOutboxFile;
use App\Models\MasterOutboxFileEntityType;
use App\Models\MasterOutboxFileType;
use App\Models\MasterOutboxFileVisibilityType;
use Carbon\Carbon;
use Exception;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response as PdfResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Properties\BooleanProperty;
use Sxope\Http\Attributes\Schemas\Operations\SxopeDelete;
use Sxope\Http\Attributes\Schemas\Operations\SxopeGet;
use Sxope\Http\Attributes\Schemas\Operations\SxopePost;
use Sxope\Http\Attributes\Schemas\Requests\FormDataRequestBody;
use Sxope\Http\Attributes\Schemas\Responses\EntityListResponse;
use Sxope\Http\Attributes\Schemas\Responses\EntityResponse;
use Sxope\Http\Attributes\Schemas\Responses\Error404Response;
use Sxope\Http\Attributes\Schemas\Responses\Error422Response;
use Sxope\Http\Attributes\Schemas\Responses\Error500Response;
use Sxope\Http\Attributes\Schemas\Responses\OkResponse;

class MasterOutboxFilesController extends BaseController
{
    #[SxopePost(
        path: '/api/v1/master-outbox-files',
        operationId: 'master-outbox-file-add',
        summary: 'Add file',
        security: ['ApiKeyAuth' => []],
        requestBody: new FormDataRequestBody(MasterOutboxFileRequestSchema::class),
        tags: ['Upload API - v1 - Master Outbox Files'],
        responses: [
            new EntityResponse(MasterOutboxFileSchema::class, '1.0'),
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function create(MasterOutboxFileCreateRequest $request): JsonResponse
    {
        try {
            $dataOwnerId = DataOwner::getDataOwnerId();
            $dayId = Day::getDayId();
            $currentTime = Carbon::now();
            $currentUserIdBytes = getCurrentUserIdBytes();

            $attachment = $request->file('attachment');

            $masterOutboxFile = MasterOutboxFile::query()
                ->create([
                    'entity_id' => BytesHelper::getBytes($request->get('entity_id')),
                    'master_outbox_file_entity_type_id' => intval($request->get('master_outbox_file_entity_type_id')),
                    'master_outbox_file_type_id' => intval($request->get('master_outbox_file_type_id')),
                    'master_outbox_file_visibility_type_id' => intval($request->get('master_outbox_file_visibility_type_id')),

                    'data_owner_id' => $dataOwnerId,
                    'description' => $request->get('description'),
                    'assigned_period' => $request->get('assigned_period'),

                    'original_file_name' => $attachment->getClientOriginalName(),
                    'file_size' => $attachment->getSize(),
                    'md5_checksum' => new Bytes(md5($attachment->get(), true)),

                    'created_at' => $currentTime,
                    'created_at_day_id' => $dayId,
                    'created_by' => $currentUserIdBytes,
                    'updated_at' => $currentTime,
                    'updated_at_day_id' => $dayId,
                    'updated_by' => $currentUserIdBytes,
                ]);

            Storage::disk(File::UPLOADS_DISK)->put($masterOutboxFile->getAttachmentPath(), $attachment->get());

            return $this->respondSuccess($masterOutboxFile->only(MasterOutboxFile::$onlyFields));
        } catch (Exception $e) {
            logException($e);
            return $this->respondError(['Server error'], 500);
        }
    }

    #[SxopePost(
        path: '/api/v1/master-outbox-files/{masterOutboxFileId}',
        operationId: 'master-outbox-file-update',
        summary: 'Update file',
        security: ['ApiKeyAuth' => []],
        requestBody: new FormDataRequestBody(MasterOutboxFileUpdateRequestSchema::class),
        tags: ['Upload API - v1 - Master Outbox Files'],
        parameters: [
            new Parameter(
                name: 'masterOutboxFileId', description: 'File entry id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            )
        ],
        responses: [
            new EntityResponse(MasterOutboxFileSchema::class, '1.0'),
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function update(string $masterOutboxFileId, MasterOutboxFileUpdateRequest $request): JsonResponse
    {
        try {
            $dayId = Day::getDayId();
            $currentTime = Carbon::now();
            $currentUserIdBytes = getCurrentUserIdBytes();

            $masterOutboxFile = MasterOutboxFile::query()
                ->where([
                    'master_outbox_file_id' => BytesHelper::getBytes($masterOutboxFileId),
                ])
                ->first();

            if (!$masterOutboxFile) {
                return $this->respondNotFound(['File not found']);
            }

            $masterOutboxFile
                ->update([
                    'master_outbox_file_type_id' => intval($request->get('master_outbox_file_type_id')),
                    'master_outbox_file_visibility_type_id' => intval($request->get('master_outbox_file_visibility_type_id')),
                    'description' => $request->get('description'),
                    'assigned_period' => $request->get('assigned_period'),

                    'updated_at' => $currentTime,
                    'updated_at_day_id' => $dayId,
                    'updated_by' => $currentUserIdBytes,
                ]);

            return $this->respondSuccess($masterOutboxFile->only(MasterOutboxFile::$onlyFields));
        } catch (Exception $e) {
            logException($e);
            return $this->respondError(['Server error'], 500);
        }
    }

    #[SxopeDelete(
        path: '/api/v1/master-outbox-files/{masterOutboxFileId}',
        operationId: 'master-outbox-file-delete',
        summary: 'Delete file data',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v1 - Master Outbox Files'],
        parameters: [
            new Parameter(
                name: 'masterOutboxFileId', description: 'File entry id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            )
        ],
        responses: [
            OkResponse::class,
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function delete(string $masterOutboxFileId): JsonResponse
    {
        try {
            $masterOutboxFile = MasterOutboxFile::query()
                ->where('master_outbox_file_id', BytesHelper::getBytes($masterOutboxFileId))
                ->first();

            if (!$masterOutboxFile) {
                return $this->respondNotFound(['Note not found']);
            }

            $masterOutboxFile->update([
                'deleted_at_day_id' => Day::getDayId(),
                'deleted_at' => Carbon::now('UTC'),
                'deleted_by' => getCurrentUserIdBytes(),
            ]);

            return $this->respondSuccess(['status' => 'Deleted Successfully']);
        } catch (Exception $e) {
            logException($e);
            return $this->respondError(['Server error'], 500);
        }
    }

    #[SxopeGet(
        path: '/api/v1/master-outbox-files/{masterOutboxFileId}/download-attachment',
        operationId: 'master-outbox-file-download-attachment-file',
        summary: 'Download attachment file',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v1 - Master Outbox Files'],
        parameters: [
            new Parameter(
                name: 'masterOutboxFileId', description: 'File entry id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            )
        ],
        responses: [
            OkResponse::class,
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function getAttachment(string $masterOutboxFileId)
    {
        try {
            $masterOutboxFile = MasterOutboxFile::query()
                ->where([
                    'master_outbox_file_id' => BytesHelper::getBytes($masterOutboxFileId),
                ])
                ->first();

            if (!$masterOutboxFile) {
                return $this->respondNotFound(['File not found']);
            }

            try {
                $fileContent = Storage::disk(File::UPLOADS_DISK)
                    ->get($masterOutboxFile->getAttachmentPath());

                $masterOutboxFile->updateUserLogEntry(getCurrentUserIdBytes(), true);
            } catch (Exception $e) {
                $fileContent = '';
            }

            return PdfResponse::make(base64_encode($fileContent), 200, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (Exception $e) {
            logException($e);
            return $this->respondError(['Server error', $e->getMessage()], 500);
        }
    }

    #[SxopePost(
        path: '/api/v1/master-outbox-files/{masterOutboxFileId}/update-view-status',
        operationId: 'master-outbox-file-update-view-status',
        summary: 'Update file view status for current user',
        security: ['ApiKeyAuth' => []],
        requestBody: new FormDataRequestBody(new Schema(properties: [new BooleanProperty('view_status')])),
        tags: ['Upload API - v1 - Master Outbox Files'],
        parameters: [
            new Parameter(
                name: 'masterOutboxFileId', description: 'File entry id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            )
        ],
        responses: [
            new OkResponse(),
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function updateUserViewStatus(string $masterOutboxFileId, MasterOutboxFileUpdateViewStatusRequest $request): JsonResponse
    {
        $masterOutboxFile = MasterOutboxFile::query()
            ->where([
                'master_outbox_file_id' => BytesHelper::getBytes($masterOutboxFileId),
            ])
            ->first();

        if (!$masterOutboxFile) {
            return $this->respondNotFound(['File not found']);
        }

        $newViewStatus = filter_var($request->get('view_status'), FILTER_VALIDATE_BOOLEAN);

        $masterOutboxFile->updateUserLogEntry(getCurrentUserIdBytes(), $newViewStatus);

        return $this->respondSuccess($newViewStatus);
    }

    #[SxopeGet(
        path: '/api/v1/master-outbox-files',
        operationId: 'master-outbox-files-get',
        summary: 'Get files data',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v1 - Master Outbox Files'],
        parameters: new MasterOutboxGetFilesListRequestParameters('getList'),
        responses: [
            new EntityResponse(GetFilesDataResponseSchema::class, '1.0'),
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function index(MasterOutboxFilesGetRequest $request): JsonResponse
    {
        $masterOutboxFilesQuery = MasterOutboxFile::query()
            ->select([
                'master_outbox_files.*'
            ])
            ->selectRaw("
                (
                    IFNULL(
                        (
                            SELECT is_read
                            FROM master_outbox_file_view_logs
                            WHERE entity_id = master_outbox_files.entity_id
                                AND data_owner_id = master_outbox_files.data_owner_id
                                AND master_outbox_file_id = master_outbox_files.master_outbox_file_id
                                AND created_by = FROM_HEX(?)
                        ),
                        FALSE
                    )
                ) AS is_read
            ", [BytesHelper::getHex(getCurrentUserIdBytes())])
            ->where([
                'data_owner_id' => DataOwner::getDataOwnerId(),
            ]);

        $bytesFilters = [
            'entity_id' => 'entity_id',
        ];

        EloquentHelper::applyBytesInFilters($masterOutboxFilesQuery, $bytesFilters, $request);
        EloquentHelper::applyIntegerInFilters(
            $masterOutboxFilesQuery,
            [
                'master_outbox_file_visibility_type_id' => 'master_outbox_file_visibility_type_id',
                'master_outbox_file_entity_type_id' => 'master_outbox_file_entity_type_id',
                'master_outbox_file_type_id' => 'master_outbox_file_type_id',
            ],
            $request
        );

        if ($request->get('pcp_id')) {
            $payerIds = DataApiClientFacade::getPcpsData($request->get('pcp_id'))->getActivePayers();
            if (empty($payerIds)) {
                $masterOutboxFilesQuery->whereNull('entity_id');
            } else {
                $masterOutboxFilesQuery->whereIn('entity_id',
                    array_map(
                        function ($payerId) {
                            return BytesHelper::getBytes($payerId);
                        },
                        $payerIds
                    )
                );
            }
        }

        /**
         * Show all public items and private for creator
         */
        $masterOutboxFilesQuery->where(function ($query) {
            $query->where('master_outbox_file_visibility_type_id', MasterOutboxFileVisibilityType::getIdByType(MasterOutboxFileVisibilityType::TYPE_PUBLIC))
                ->orWhere(function ($query) {
                    $query->where('master_outbox_file_visibility_type_id', MasterOutboxFileVisibilityType::getIdByType(MasterOutboxFileVisibilityType::TYPE_PRIVATE))
                        ->where('created_by', getCurrentUserIdBytes());
                });
        });

        if ($request->has('created_at')) {
            $masterOutboxFilesQuery->whereBetween(
                'start_time',
                [
                    Carbon::parse($request->get('created_at')['from'])->format('Y-m-d 00:00:00'),
                    Carbon::parse($request->get('created_at')['to'])->format('Y-m-d 23:59:59'),
                ]
            );
        }

        if ($request->has('sort')) {
            foreach ($request->get('sort') as $sortColumn) {
                $masterOutboxFilesQuery->orderBy($sortColumn['field'], $sortColumn['direction']);
            }
        } else {
            $masterOutboxFilesQuery->orderBy('created_at', 'DESC');
        }

        return $this->respondSuccess(PageHelper::applyOnly(
            $masterOutboxFilesQuery->paginate($request->get('per_page', 15)),
            MasterOutboxFile::$onlyFields
        ));
    }

    #[SxopeGet(
        path: '/api/v1/master-outbox-files/aggregated',
        operationId: 'master-outbox-files-get-aggregated',
        summary: 'Get files data',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v1 - Master Outbox Files'],
        parameters: new MasterOutboxGetFilesListRequestParameters('getAggregatedList'),
        responses: [
            new EntityListResponse(MasterOutboxFilesAggregatedSchema::class, '1.0'),
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function indexAggregated(MasterOutboxFilesGetRequest $request): JsonResponse
    {
        $masterOutboxFilesQuery = MasterOutboxFile::query()
            ->select([
                '*'
            ])
            ->selectRaw("
                (
                    SELECT is_read
                    FROM master_outbox_file_view_logs
                    WHERE entity_id = master_outbox_files.entity_id
                        AND data_owner_id = master_outbox_files.data_owner_id
                        AND master_outbox_file_id = master_outbox_files.master_outbox_file_id
                        AND created_by = FROM_HEX(?)
                ) AS is_read
            ", [BytesHelper::getHex(getCurrentUserIdBytes())])
            ->where([
                'data_owner_id' => DataOwner::getDataOwnerId(),
            ]);

        if ($request->has('created_at')) {
            $masterOutboxFilesQuery->whereBetween(
                'start_time',
                [
                    Carbon::parse($request->get('created_at')['from'])->format('Y-m-d 00:00:00'),
                    Carbon::parse($request->get('created_at')['to'])->format('Y-m-d 23:59:59'),
                ]
            );
        }

        if ($request->get('pcp_id')) {
            $payerIds = DataApiClientFacade::getPcpsData($request->get('pcp_id'))->getActivePayers();
            if (empty($payerIds)) {
                $masterOutboxFilesQuery->whereNull('entity_id');
            } else {
                $masterOutboxFilesQuery->whereIn('entity_id',
                    array_map(
                        function ($payerId) {
                            return BytesHelper::getBytes($payerId);
                        },
                        $payerIds
                    )
                );
            }
        }

        $masterOutboxEntityData = MasterOutboxFile::query()
            ->fromSub($masterOutboxFilesQuery, 'master_outbox_files')
            ->select([
                'entity_id',
                DB::raw('COUNT(1) AS total_files'),
                DB::raw('SUM(IF(is_read = TRUE, 1, 0)) AS total_read_files'),
                DB::raw('FORMAT_TIMESTAMP("%F %X", MAX(created_at), "UTC") AS updated_at'),
            ])
            ->groupBy('entity_id')
            ->get();

        return $this->respondSuccess(PageHelper::applyOnly($masterOutboxEntityData, MasterOutboxFile::$onlyAggFields));
    }

    #[SxopeGet(
        path: '/api/v1/master-outbox-files/visibility-types',
        operationId: 'master-outbox-files-visibility-types-list',
        summary: 'Get files visibility types list',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v1 - Master Outbox Files'],
        responses: [
            new EntityListResponse(MasterOutboxFileVisibilityTypeSchema::class, '1.0'),
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function visibilityTypes(): JsonResponse
    {
        return $this->respondSuccess(
            MasterOutboxFileVisibilityType::all(),
            [
                'Cache-Control' => 'max-age=3600, private'
            ]
        );
    }

    #[SxopeGet(
        path: '/api/v1/master-outbox-files/types',
        operationId: 'master-outbox-files-types-list',
        summary: 'Get files types list',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v1 - Master Outbox Files'],
        responses: [
            new EntityListResponse(MasterOutboxFileTypeSchema::class, '1.0'),
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function types(): JsonResponse
    {
        return $this->respondSuccess(
            MasterOutboxFileType::all(),
            [
                'Cache-Control' => 'max-age=3600, private'
            ]
        );
    }

    #[SxopeGet(
        path: '/api/v1/master-outbox-files/entity-types',
        operationId: 'master-outbox-files-entity-types-list',
        summary: 'Get files entity types list',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v1 - Master Outbox Files'],
        responses: [
            new EntityListResponse(MasterOutboxFileEntityTypeSchema::class, '1.0'),
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function entityTypes(): JsonResponse
    {
        return $this->respondSuccess(
            MasterOutboxFileEntityType::all(),
            [
                'Cache-Control' => 'max-age=3600, private'
            ]
        );
    }
}
