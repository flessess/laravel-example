<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Attributes\Schemas\Models\V1\Files\FileMetadataModel;
use OpenApi\Attributes\Parameter;
use OpenApi\Attributes\Schema;
use Sxope\Http\Attributes\Schemas\Operations\SxopePost;
use Sxope\Http\Attributes\Schemas\Requests\FormDataRequestBody;
use App\Http\Attributes\Schemas\Requests\V1\FileUploadRequestSchema;
use App\Http\Attributes\Schemas\Responses\V1\UploadedFile;
use App\Http\Requests\Api\V1\UploadFileRequest;
use App\Mail\FileReceivedMail;
use App\Models\File;
use App\Models\FileMetadata;
use App\Services\FileUploaderService;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;
use Sxope\Facades\Debugger;
use Sxope\Http\Attributes\Schemas\Operations\SxopeGet;
use Sxope\Http\Attributes\Schemas\Responses\EntityResponse;
use Sxope\Http\Attributes\Schemas\Responses\Error404Response;
use Sxope\Http\Attributes\Schemas\Responses\Error422Response;
use Sxope\Http\Attributes\Schemas\Responses\Error500Response;
use Sxope\ValueObjects\Id;

class FilesController extends BaseController
{
    #[SxopePost(
        path: '/api/v1/files',
        operationId: 'upload-file',
        description: 'Upload files',
        security: ['ApiKeyAuth' => []],
        requestBody: new FormDataRequestBody(FileUploadRequestSchema::class),
        tags: ['Upload API - v1 - Files'],
        parameters: [
            new Parameter(
                name: 'return_file_info', in: 'query', required: false,
                schema: new Schema(type: 'integer', default: 1, example: 1)
            )
        ],
        responses: [
            new EntityResponse(UploadedFile::class, '1.0'),
            Error422Response::class,
            Error500Response::class,
        ]
    )]
    public function upload(UploadFileRequest $request, FileUploaderService $fileUploaderService): JsonResponse
    {
        $labels = [
            'action_type' => 'files',
            'action' => 'files.upload',
        ];

        ini_set('memory_limit', '8192M');

        $skipOcrProcessing = in_array(
            strtolower($request->file('file')?->getClientOriginalExtension()),
            [
                'zip',
                'csv',
                'tsv',
            ]
        ) || $request->get('skip_tagging');

        $operationId = Uuid::uuid4()->toString();

        $metadata = [];

        foreach (FileMetadata::ALLOWED_FIELDS as $allowedField) {
            $metadata[$allowedField] = $request->get($allowedField);
        }

        if (empty($metadata['user_id']) && !empty(getCurrentUserId())) {
            $metadata['user_id'] = getCurrentUserId();
        }

        try {
            $fileExistWithName = File::query()
                ->where([
                    'md5_checksum' => new Bytes(md5($request->file('file')?->get(), true)),
                ])
                ->limit(1)
                ->value('original_file_name');

            if ($fileExistWithName) {
                Debugger::error(
                    'File uploads error: file already exist',
                    ['file_name' => $fileExistWithName],
                    $labels
                );
                return $this->respondError([sprintf('File already exist: %s', $fileExistWithName)], 422);
            }
            $fileData = $fileUploaderService->upload(
                $request->file('file'),
                $operationId,
                $request->get('source'),
                $metadata,
                $skipOcrProcessing,
                (int) $request->get('file_type_id'),
                $request->get('data_owner_id')
            );

            Debugger::info(
                'File uploaded',
                [
                    'file_id' => Id::create($fileData['file_id'])->getHex(),
                    'metadata' => $metadata,
                ],
                $labels
            );

            if (!empty($metadata['email']) && $request->get('source') === 'EMAIL') {
                Mail::to($metadata['email'])->queue(new FileReceivedMail());
            }

            if($request->get('return_file_info')) {
                return $this->respondSuccess($fileData);
            }

            return $this->respondSuccess(['message' => 'Your file has been added to queue']);

        } catch (\Exception $e) {
            Debugger::error(
                'File upload error: ' . $e->getMessage(),
                [],
                $labels
            );
            logException($e);
            return $this->respondError(['Cannot process your file', $e->getMessage(), $e->getLine()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[SxopeGet(
        path: '/api/v1/files/{fileId}',
        operationId: 'get-file-metadata',
        description: 'Get file metadata',
        security: ['ApiKeyAuth' => []],
        tags: ['Upload API - v1 - Files'],
        parameters: [
            new Parameter(
                parameter: 'fileId', name: 'fileId', description: 'File id', in: 'path', required: true,
                schema: new Schema(type: 'string', example: '01aff7a9-6fb8-4edc-8b48-06d4b0535d33')
            ),
        ],
        responses: [
            [EntityResponse::class, FileMetadataModel::class, '1.0'],
            Error404Response::class,
            Error422Response::class,
            Error500Response::class,
        ],
    )]
    /**
     * @param string $fileId
     * @param FileUploaderService $fileUploaderService
     *
     * @return JSONResponse
     */
    public function metadata(string $fileId, FileUploaderService $fileUploaderService): JsonResponse
    {
        return $this->respondSuccess($fileUploaderService->getFileData(Id::create($fileId)));
    }
}
