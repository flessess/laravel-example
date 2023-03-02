<?php

namespace App\Jobs;

use App\Api\Ocr\OcrApiInterface;
use App\Api\OcrElastic\OcrElasticApiInterface;
use App\Helpers\BytesHelper;
use App\Http\Traits\PdfPagesCount;
use App\Models\Day;
use App\Models\File;
use App\Models\FilePage;
use App\Models\FilesTasksStatusesHistory;
use App\Models\FileStatus;
use App\Models\FileTask;
use App\Models\FileTaskStatus;
use App\Models\OcrFileStatus;
use Exception;
use Google\Cloud\Spanner\Bytes;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Sxope\Job\PubSub\AbstractPubSubMessageHandlerJob;

/**
 * Class GetFileDataFromOcr
 *
 * @package App\Jobs
 */
class GetFileDataFromOcr extends AbstractPubSubMessageHandlerJob
{
    use PdfPagesCount;

    /**
     * @param OcrApiInterface $ocrApi
     * @return bool|void
     *
     * @throws Exception
     */
    public function handle(OcrApiInterface $ocrApi, OcrElasticApiInterface $ocrElasticApi)
    {
        ini_set('memory_limit', '8192M');
        try {
            $message = $this->message->getData();
            $ocrFileStatus = !empty($message['error_type']) ? $message['error_type'] : $message['status'];
            $fileStatus = !OcrFileStatus::isErrorStatus($ocrFileStatus) ? FileStatus::STATUS_SUCCESS : FileStatus::STATUS_ERROR;

            $ocrFileStatusId = OcrFileStatus::getIdByStatus($ocrFileStatus);
            $fileStatusId = FileStatus::getIdByStatus($fileStatus);

            $fileId = pathinfo($message['fguid'], PATHINFO_FILENAME);
            $fileIdWithExtension = $message['fguid'];

            if ($ocrFileStatus === OcrFileStatus::STATUS_REUPLOAD_REQUIRED) {
                logFileData($fileId, "Reupload requested: " . json_encode($message));
                $fileExistInOcr = false;
                $ocrResponse = [];
                try {
                    if (!Storage::disk(File::UPLOADS_DISK)->exists($fileIdWithExtension)) {
                        try {
                            Artisan::call(
                                'fix-data:reload-legacy-files-to-bucket',
                                [
                                    'fileIds' => $fileId,
                                ]
                            );
                        } catch (Exception $e) {
                            logFileData($fileId, $e->getMessage());
                        }
                    }
                    $ocrResponse = $ocrApi->uploadFile(
                        Storage::disk(File::UPLOADS_DISK)->get($fileIdWithExtension),
                        $fileIdWithExtension,
                        File::getFileDataOwnerId($fileId)
                    );
                } catch (Exception $e) {
                    if ($e->getCode() === Response::HTTP_UNPROCESSABLE_ENTITY) {
                        $fileExistInOcr = true;
                    }
                    logFileData($fileId, $e->getMessage());
                }
                logFileData(
                    $fileId,
                    json_encode($ocrResponse)
                );
                //if file already exist - continue flow
                if (!$fileExistInOcr) {
                    return true;
                }
            }

            $fileTask = FileTask::query()
                ->where("file_id", BytesHelper::getBytes($fileId))
                ->first();

            $fileDataOwnerId = File::getFileDataOwnerId($fileId);

            //todo-temp file already processed and should be skipped
            if ($fileTask) {
                logFileData($fileId, sprintf("Processing(file already exists): %s", $fileIdWithExtension));
            }

            logFileData($fileId, "File status: $fileStatus \nOcr File Status: $ocrFileStatus");
            if ($fileStatus === FileStatus::STATUS_ERROR) {
                File::query()
                    ->where("file_id", BytesHelper::getBytes($fileId))
                    ->update([
                        'file_status_id' => $fileStatusId,
                        'ocr_file_status_id' => $ocrFileStatusId,
                    ]);
                return true;
            }

            $filePages = $ocrApi->getPagesList($fileIdWithExtension, $fileDataOwnerId);

            $jsonMetadata = json_encode($message);
            $pagesCount = count($filePages['data']);
            logFileData($fileId, "Metadata: $jsonMetadata \nPages count: $pagesCount");

            File::query()
                ->where("file_id", BytesHelper::getBytes($fileId))
                ->update([
                    'file_status_id' => $fileStatusId,
                    'ocr_file_status_id' => $ocrFileStatusId,
                    'pages_count' => count($filePages['data']),
                    'ocr_datapoints_count' => $ocrElasticApi->getDatapointsCount($fileIdWithExtension, $fileDataOwnerId),
                ]);

            //file is failed in ocr
            if (!$pagesCount) {
                return true;
            }

            $file = File::query()
                ->select(['file_id'])
                ->where("file_id", BytesHelper::getBytes($fileId))
                ->first();

            foreach ($filePages['data'] as $pageNumber => $pageData) {
                $pagePath = $pageData['url'];

                $pageContent = $ocrApi->getPageContent($pagePath, $fileDataOwnerId);

                $pageRecord = FilePage::query()
                    ->updateOrCreate(
                        [
                            'file_id' => $file->file_id,
                            'page_number' => $pageNumber,
                        ],
                        [
                            'page_sha256_hash' => new Bytes(hash('sha256', $pageContent, true)),
                            'md5_checksum' => new Bytes(md5($pageContent, true)),
                            'crc32_checksum' => crc32($pageContent),
                            'sequence_number' => $pageNumber,
                            'x_resolution' => 0,
                            'y_resolution' => 0,
                            'detected_dpi' => 0,
                            'created_day_id' => Day::getDayId(),
                        ]);

                Storage::disk(File::UPLOADS_DISK)->put("{$fileId}/{$pageRecord->page_id}.jpg", $pageContent);
            }

            if(!$fileTask) {
                $fileTask = FileTask::query()
                    ->firstOrCreate(
                        [
                            'file_id' => $file->file_id,

                        ],
                        [
                            'task_status_id' => FileTaskStatus::getIdByStatus(FileTaskStatus::STATUS_NOT_STARTED),
                            'created_at_day_id' => Day::getDayId(),
                        ]
                    );

                FilesTasksStatusesHistory::query()
                    ->create([
                        'file_id' => $fileTask->file_id,
                        'task_id' => $fileTask->task_id,
                        'task_status_id' => FileTaskStatus::getIdByStatus(FileTaskStatus::STATUS_NOT_STARTED),
                        'updated_at_day_id' => Day::getDayId(),
                        'updated_at' => now()
                    ]);
            }
        } catch (Exception $e) {
            logException($e);
            logFileData($fileId, $e->getMessage());
            throw $e;
        }
    }
}
